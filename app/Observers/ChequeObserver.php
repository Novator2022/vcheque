<?php

namespace App\Observers;

use Log;
use Notification;
use App\Exports\ChequeXML;
use App\User;
use App\Param;
use App\Cheque;
use App\Organisation;
use App\Nomenclature;
use App\Jobs\ModulkassaJob;
use App\Services\Modulkassa;
use App\Jobs\ExportJob;
use App\Transactions;

use App\Notifications\Customers\ChequeComplete;
use App\Notifications\Customers\ChequeInprogress;
use App\Notifications\Customers\ChequeError;
use App\Notifications\Managers\ChequeError as ManagerChequeError;

class ChequeObserver
{
    /**
     * Handle the cheque "created" event.
     *
     * @param  \App\Cheque  $cheque
     * @return void
     */
    public function created(Cheque $cheque)
    {
        $procent = 0;

        $user = $cheque->user;
        if ($user->procent) {
            $procent = $user->procent;
        } else {
            $procent = Param::where('name', 'default_procent')->first()->value;
        }

        $summ = 0;

        if (isset($cheque->data['positions'])) {
            if (sizeof($cheque->data['positions']) > 0) {
                foreach ($cheque->data['positions'] as $pindex => $position) {
                    $summ += $position['amount'] * $position['quantity'];
                }
            }
        }

        $take = $summ * ($procent / 100);

        $user->frozen += $take;
        $user->save();

        Cheque::withoutEvents(function () use ($cheque, $take) {
            $cheque->take = $take;
            $cheque->save();
        });

        try {
            Log::debug('ChequeObserver #' . $cheque->id . ': slot ' . $cheque->time_slot->slot->time_from . ' - ' . $cheque->time_slot->slot->time_to);
        } catch (\Exception $e) {
            Log::error($e);
        }

        $this->printCheque($cheque);

        try {
            Nomenclature::updateFromCheque($cheque->data);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Handle the cheque "updated" event.
     *
     * @param  \App\Cheque  $cheque
     * @return void
     */
    public function updated(Cheque $cheque)
    {
    	$exported = false;

        $user = $cheque->user;
        if ($cheque->status == Cheque::STATUS_NEW) {
            $this->printCheque($cheque);
        } else if ($cheque->status == Cheque::STATUS_SUCCESS) {
            // $user->notify((new ChequeComplete($cheque)));

            $t_cnt = Transactions::where('cheque_id', $cheque->id);
            if ($t_cnt->count() == 0) {
                // добавляем транзакцию
                Transactions::create(['user_id' => $user->id, 'cheque_id' => $cheque->id, 'amount' => -(float)$cheque->take, 'description' => 'Списание за чек ' . $cheque->id]);

                // обновляем баланс, заморозку
                $user->balance = Transactions::where('user_id', $user->id)->sum('amount');
                $user->frozen -= $cheque->take;
                $user->save();
            }

            // автоотправка в маркет
            if (($cheque->organisation) && ($cheque->organisation->auto_export) && (!$exported)) {
                ExportJob::dispatch($cheque)
                    ->onConnection('redis');
                $exported = true;
            }
        } else if ($cheque->status == Cheque::STATUS_FAILED) {
            // $user->notify((new ChequeError($cheque)));
            // Notification::send(User::where('role', 'admin')->get(), new ManagerChequeError($cheque));
        }

        if ($cheque->isDirty('status')) {
            if ($cheque->status == Cheque::STATUS_SUCCESS) {
                // увеличим сумму выбитых чеков
                \Log::debug($user->id . ' +' . ((float)$cheque->amount) . ' (#' . $cheque->id . ')');
                $user->limit_current = $user->limit_current + (float)$cheque->amount;
                $user->save();
            }
        }

        if (($cheque->isDirty('export')) && ($cheque->export == 1) && (!$exported)) {
            ExportJob::dispatch($cheque)
                ->onConnection('redis');
            $exported = true;
        }

        event(new \App\Events\LogEvent($cheque, $cheque->user, $action = $cheque->status, $type="cheque"));
    }

    /**
     * Handle the cheque "deleted" event.
     *
     * @param  \App\Cheque  $cheque
     * @return void
     */
    public function deleted(Cheque $cheque)
    {
        //
    }

    /**
     * Handle the cheque "restored" event.
     *
     * @param  \App\Cheque  $cheque
     * @return void
     */
    public function restored(Cheque $cheque)
    {
        //
    }

    /**
     * Handle the cheque "force deleted" event.
     *
     * @param  \App\Cheque  $cheque
     * @return void
     */
    public function forceDeleted(Cheque $cheque)
    {
        //
    }

    protected function printCheque($cheque)
    {
        // return false; 
        
        if ($cheque->status != Cheque::STATUS_NEW) {
            return;
        }

        if (empty($cheque->organisation)) {
            return;
        }

        if ($cheque->organisation->modulkassa == true) {
            // $delay = 0;
            // $currentHour = intval(now()->format('H'));

            // if ($cheque->type != Cheque::TYPE_MANUAL) {
            //     Log::debug('Check work time ' . now() . ' between ' . env('WORK_TIME_START', 9) . ' and ' . env('WORK_TIME_END', 20) . ' [hour is ' . $currentHour . ']');

            //     if ($currentHour >= env('WORK_TIME_END', 20)) {
            //         $delay = 24 - $currentHour + env('WORK_TIME_START', 9);
            //         Log::debug('delay job on next day in ' . $delay . ' hours');
            //     } elseif ($currentHour < env('WORK_TIME_START', 9)) {
            //         $delay = env('WORK_TIME_START', 9) - $currentHour;
            //         Log::debug('delay job until morning in ' . $delay . ' hours');
            //     }
            // }

            // if ($delay == 0) {
                $cheque->status = Cheque::STATUS_REQUEST;
                $cheque->save();
                ModulkassaJob::dispatch($cheque)
                    ->onConnection('redis');
            // } else {
            //     ModulkassaJob::dispatch($cheque)
            //         ->onConnection('redis')
            //         ->delay(now()->addHours($delay));
            // }

            // $cheque->user->notify((new ChequeInprogress($cheque)));
        } else {
            $export = new ChequeXML(Cheque::where('organisation_id', $cheque->organisation_id)->get());
            $file = $export->store(($cheque->organisation->data && isset($cheque->organisation->data["inn"])) ? $cheque->organisation->data["inn"] : '1234567');
        }
    }
}

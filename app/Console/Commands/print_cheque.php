<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cheque;
use App\TimeSlots;
use App\ChequeSlot;
use App\Jobs\ModulkassaJob;
use App\Services\Modulkassa;

class print_cheque extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cheque:print_cheque';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    protected function printCheque($cheque)
    {
        if ($cheque->status != Cheque::STATUS_NEW) {
            return;
        }

        if (empty($cheque->organisation)) {
            return;
        }

        if ($cheque->organisation->modulkassa == true) {
            $cheque->status = Cheque::STATUS_REQUEST;
            $cheque->save();
            ModulkassaJob::dispatch($cheque)
                ->onConnection('redis');
        }
    }

    public function l($s) {
        print $s . "\n";
        \Log::debug($s);
    }

    public function handle()
    {
    	// сначала почисть очередь
    	// return false;

        $nopause = false;

        $start_pause = rand(0, 120);
        date_default_timezone_set('Europe/Moscow');

        $this->l("[" . date('H:i:s') . "] cheque:print_cheque start");

        $cheque_slots = ChequeSlot::whereNull('completed_at')
            ->leftJoin('time_slots', 'time_slots.id', '=', 'cheque_slot.slot_id')
            ->where('time_slots.time_from', '<=', date('H:i:s'))
            ->orderBy('time_from')
            ->get();
        $cheques_count = $cheque_slots->count();

        $this->l("[" . date('H:i:s') . "] cheques count: $cheques_count items");

        if ($cheques_count > 0) {
            $this->l("[" . date('H:i:s') . "] start pause: $start_pause secs");
            if (!$nopause) sleep($start_pause);

            $period = 60 * 10; // 10 минут
            $period -= $start_pause;

            $pause_between = round($period / $cheques_count);
            if ($pause_between < 5) {
                $pause_between = 5;
            } elseif ($pause_between > 120) {
                $pause_between = 120;
            }

            $this->l("[" . date('H:i:s') . "] max pause between: $pause_between secs");

            foreach ($cheque_slots as $cheque_slot) {
                $cheque_slot->fresh();
                $cheque = $cheque_slot->cheque;

                if ($cheque->status == 'new') {
                    $this->l("[" . date('H:i:s') . "] print cheque: #" . $cheque_slot->cheque_id . " (" . $cheque_slot->time_from . " - " . $cheque_slot->time_to . ")");
                    $current_pause = rand(0, $pause_between);
                    $this->l("[" . date('H:i:s') . "] pause: $current_pause secs");

                    $this->printCheque($cheque);

                    ChequeSlot::where('cheque_id', $cheque_slot->cheque_id)->update(['completed_at' => \DB::raw('NOW()')]);
                    if (!$nopause) sleep($current_pause);
                } else {
                    $this->l("[" . date('H:i:s') . "] error: status {" . $cheque->status . "}, skipped");
                    ChequeSlot::where('cheque_id', $cheque_slot->cheque_id)->update(['completed_at' => \DB::raw('NOW()')]);
                }
            }
        }

        $this->l("[" . date('H:i:s') . "] cheque:print_cheque finish");
    }
}
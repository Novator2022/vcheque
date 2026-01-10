<?php

namespace App\Console\Commands\Scheduled;

use Log;
use Illuminate\Console\Command;
use App\Services\Modulkassa;
use App\Cheque;
use App\ChequeSchedule;
use App\Contractor;
use App\Organisation;
use App\Events\LogEvent;
use Illuminate\Support\Carbon;
use App\ChatGpt;

class ScheduleCheque extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cheque:schedule {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule cheques';

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
    public function handle()
    {
        $start = microtime(true);

        $force = $this->option('force');

        $hours = 3;
        $time = Carbon::now()->setTimezone('UTC');
        $today = Carbon::create($time->year, $time->month, $time->day, 0, 0, 0); //set time to 00:00
        $morning = Carbon::create($time->year, $time->month, $time->day, env('WORK_TIME_START', 9), 0, 0); //->setTimezone('UTC'); //set time to 08:00
        $evening = Carbon::create($time->year, $time->month, $time->day, env('WORK_TIME_END', 21), 0, 0); //->setTimezone('UTC'); //set time to 18:00

        $organisations = Organisation::where('modulkassa', true)->get();

        $bar = $this->output->createProgressBar($organisations->count());
        $bar->start();

        foreach ($organisations as $organisation) {

            $bar->advance();

            $chequeSchedule = ChequeSchedule::where('period_start', '<=', now()->format('Y-m-d'))
                ->where('period_end', '>=', now()->format('Y-m-d'))
                ->whereNull('completed_at')
                ->where('organisation_id', $organisation->id);

            if (!$force) {
                $chequeSchedule->where('updated_at', '<', $today->format('Y-m-d H:i:s'));
                Log::info('Where: updated_at < ' . $today->format('Y-m-d H:i:s'));
            } else {
                Log::info('Where: updated_at not used - force');
            }

            Log::info('Now: ' . $time . ' [' . $morning . ' : ' . $evening . '] - ' . ($time->between($morning, $evening, true) ? 'work time' : 'day off'));

            $chequeSchedule = $chequeSchedule->first();

            if (empty($chequeSchedule)) {
                continue;
            }

            if ($chequeSchedule->periodic == 'weekly') {
                if ($chequeSchedule->updated_at->diffInDays(now()) < 7) {
                    $chequeSchedule->touch();
                    continue;
                }
            } elseif ($chequeSchedule->periodic == 'monthly') {
                if ($chequeSchedule->updated_at->diffInDays(now()) < 30) {
                    $chequeSchedule->touch();
                    continue;
                }
            }

            $data = [
                "contractor" => $chequeSchedule->data["contractor"] ?? null,
                "positions" => []
            ];

            $hours = $chequeSchedule->organisation->data['gmt'] ?? 3;

            if ($time->between($morning, $evening, true)) {
                $dataPositions = empty($chequeSchedule->data["positions"]) ? $this->fixErrorUploaded($chequeSchedule->data) : $chequeSchedule->data;
                $data["contractor"] = $dataPositions["contractor"];

				$nomenclatures = [];					
                foreach ($dataPositions["positions"] as $position) {
                    if (empty($position["nomenclature"])) {
                        continue;
                    }

                    if ($chequeSchedule->no_nds) {
                        $position["nds"] = 0;
                    }

                    $data["positions"][] = [
                        "nds" => $position["nds"] ?? 0.2,
                        "name" => $position["nomenclature"],
                        "amount" => $position["amount_from"],
                        "quantity" => $position["quantity_from"],
                        "nomenclature" =>  $position["nomenclature"],
                    ];
					$nomenclatures[] = $position["nomenclature"];											 
                }

                if (is_numeric($data["contractor"])) {
                    $contractor = Contractor::find($data["contractor"]);
                    $data["contractor"] = $contractor->toArray();
                }

                $chequeData = [
                    'user_id' => $chequeSchedule->user_id,
                    'schedule_id' => $chequeSchedule->id,
                    'data' => $data,
                    'organisation_id'  => $chequeSchedule->organisation_id,
                    'type' => Cheque::TYPE_SCHEDULE,
                ];

                if ($chequeSchedule->email) {
                    $chequeData['email'] = $chequeSchedule->email;
                }

                $category_id = (new ChatGpt)->getCategoryByNomenclature($nomenclatures);
                if (!$category_id) {
                    \Log::error('Не удалось определить категорию для номенклатуры: ');
                    \Log::error($nomenclatures);
                    
                    continue;
                } else {
                    \Log::debug('Определили категорию для номенклатуры: ');
                    \Log::debug($nomenclatures);
                    \Log::debug($category_id);

                    $chequeData['category_id'] = $category_id;
                }
 
                $cheque = Cheque::create($chequeData);
                $chequeSchedule->touch();

                if ($chequeSchedule->periodic == 'once') {
                    $chequeSchedule->update(['completed_at' => \DB::raw('NOW()')]);
                }
                $chequeSchedule->touch();
                // Log::info("Cheque: " . $cheque->toJson());

                event(new LogEvent($chequeSchedule, $chequeSchedule->user, 'processes', 'cheque_schedule'));
            }
        }
        $bar->finish();
        $this->info('');
    }

    protected function fixErrorUploaded($data)
    {
        $contractor = null;
        $positions = [];
        foreach ($data as $row) {
            $positions = array_merge($positions, $row["positions"] ?? []);
            $contractor = empty($row["contractor"]) ? $contractor : $row["contractor"];
        }
        $ret = [
            "positions" => $positions,
            "contractor" => $contractor,
        ];
        return $ret;
    }
}
/*
'user_id',
'data',
'status',
'organisation_id',
'file',
'modulkassa',
'type',
 */

/*
'user_id',
'expense_type_id',
'organisation_id',
'data',
'period_start',
'period_end',
'periodic', //'monthly');
'type'
 */


/*


{
    "positions": [
        {
            "nds": 0.2,
            "name": null,
            "amount_to": "1",
            "amount_from": 1,
            "quantity_to": 10,
            "nomenclature": "шашлык",
            "quantity_from": 1,
            "nomenclature_id": null
        },
        {
            "nds": 0.2,
            "name": null,
            "amount_to": "1",
            "amount_from": 1,
            "quantity_to": "1",
            "nomenclature": "шашлык",
            "quantity_from": 1,
            "nomenclature_id": null
        },
        {
            "nds": 0.2,
            "name": null,
            "amount_to": "1",
            "amount_from": 1,
            "quantity_to": "1",
            "nomenclature": "шашлык",
            "quantity_from": 1,
            "nomenclature_id": null
        },
        {
            "nds": 0.2,
            "name": null,
            "amount_to": "1",
            "amount_from": 1,
            "quantity_to": "1",
            "nomenclature": "шашлык",
            "quantity_from": 1,
            "nomenclature_id": null
        },
        {
            "nds": 0.2,
            "name": null,
            "amount_to": "1",
            "amount_from": 1,
            "quantity_to": "1",
            "nomenclature": "шашлык",
            "quantity_from": 1,
            "nomenclature_id": null
        }
    ],
    "contractor": {
        "id": 1,
        "inn": "77007756543",
        "kpp": "770070101",
        "name": "ООО \"МЕБЕЛЬСТРОЙ\"",
        "user": {
            "id": 4,
            "name": "узер",
            "role": "user",
            "email": "yser@mail.com",
            "limit": 500000,
            "phone": "+7 999 876 54 33",
            "bad_dept": 0,
            "created_at": "2019-08-18 08:35:38",
            "deleted_at": null,
            "manager_id": null,
            "updated_at": "2020-01-21 08:40:40",
            "email_verified_at": null
        },
        "address": "Свалка мусора",
        "percent": 10,
        "user_id": 4,
        "created_at": "2019-08-22 07:38:15",
        "deleted_at": null,
        "updated_at": "2019-10-11 06:53:55"
    }
}

{
    "positions": [
        {
            "nds": 0.2,
            "name": "Карандаш",
            "amount": 100,
            "quantity": 1,
            "nomenclature": "Карандаш",
            "nomenclature_id": 62
        },
        {
            "nds": 0.2,
            "name": "Карандаш",
            "amount": 100,
            "quantity": "2",
            "nomenclature": "Карандаш",
            "nomenclature_id": 62
        },
        {
            "nds": 0.2,
            "name": "Ластик",
            "amount": 100,
            "quantity": "2",
            "nomenclature": "Ластик",
            "nomenclature_id": 56
        }
    ],
    "contractor": {
        "id": 1,
        "inn": "77007756543",
        "kpp": "770070101",
        "name": "ООО \"МЕБЕЛЬСТРОЙ\"",
        "user": {
            "id": 4,
            "name": "узер",
            "role": "user",
            "email": "yser@mail.com",
            "limit": 500000,
            "phone": "+7 999 876 54 33",
            "bad_dept": 0,
            "created_at": "2019-08-18 08:35:38",
            "deleted_at": null,
            "manager_id": null,
            "updated_at": "2020-01-21 08:40:40",
            "email_verified_at": null
        },
        "address": "Свалка мусора",
        "percent": 10,
        "user_id": 4,
        "created_at": "2019-08-22 07:38:15",
        "deleted_at": null,
        "updated_at": "2019-10-11 06:53:55"
    }
}
 */

<?php

namespace App\Console\Commands\Scheduled;

use Log;
use Illuminate\Console\Command;
use App\Services\Modulkassa;
use App\Cheque;
use App\ChequeSchedule;
use App\Organisation;
use Illuminate\Support\Carbon;

class TouchCheque extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cheque:touch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Touch cheques';

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

        $cheques = Cheque::where('status', Cheque::STATUS_NEW)
            // ->where('updated_at', '<', now()->subHours(1))
            ->get();

        $bar = $this->output->createProgressBar($cheques->count());
        $bar->start();

        foreach ($cheques as $cheque) {
            // $this->info('Touch cheque: ' . $cheque->id);
            Log::debug('Touch cheque: ' . $cheque->id);
            $cheque->touch();
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
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

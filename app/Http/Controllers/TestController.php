<?php

namespace App\Http\Controllers;

use Log as Logger;
// use App\Log;
use App\Cheque;
use App\ChatGpt;
use App\ExportCheque;
use App\ExportCompanyInn;
use App\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ExportJob;
use App\Jobs\ExportToBtJob;
use App\Jobs\ExportStatusToBtJob;
use App\Jobs\ModulkassaJob;
use Maatwebsite\Excel\Facades\Excel;
use Log;

class TestController extends Controller
{
	protected function printCheque($cheque)
    {
        // if ($cheque->status != Cheque::STATUS_NEW) {
        //     return;
        // }

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

    public function test(Request $request) {
        $cheques = Cheque::whereIn('id', [217319])->get();
        
        foreach ($cheques as $cheque) {
            $this->printCheque($cheque);
        }
    }
}

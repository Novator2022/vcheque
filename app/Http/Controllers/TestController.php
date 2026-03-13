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
use App\Jobs\CloudpaymentsJob;
use App\Services\Cloudpayments;
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
        } elseif ($cheque->organisation->cloudpayments == true) {
            $cheque->status = Cheque::STATUS_REQUEST;
            $cheque->save();
            CloudpaymentsJob::dispatch($cheque)
                ->onConnection('redis');
        }
    }

    public function test(Request $request) {
        // // $cheque = Cheque::whereIn('id', [223105])->first();
        
        // $files = ExportCheque::get_files(223197);

        // file_put_contents(__DIR__ . '/pdf2_filename.pdf', $files['pdf2']);
        // file_put_contents(__DIR__ . '/filename.pdf', $files['png']);
        // file_put_contents(__DIR__ . '/blurred_filename.png', $files['png']);

        // die('ok!');
        // // dd($files);
    }
}

<?php

namespace App\Http\Controllers;

use Log;
use App\User;
use App\Cheque;
use App\Services\Modulkassa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ModulkassaCallbackJob;

class ModulekassaController extends Controller
{
    public function callback(Request $request, Cheque $cheque)
    {
        Log::debug('Modulekassa callback', $request->all());
        if (!empty($cheque)) {
            ModulkassaCallbackJob::dispatch($cheque)
                ->onConnection('redis');
        }
        return "Ok";
    }
}

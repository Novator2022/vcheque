<?php

namespace App\Jobs;

use Log;
use App\Cheque;
use App\Services\Cloudpayments;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CloudpaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // protected $connection = 'redis';
    protected $tries = 3;
    protected $check;

    public function __construct(Cheque $check)
    {
        $this->check = $check;
    }

    public function handle()
    {
        Log::debug('Handling cloudpayments job: ' . json_encode($this->check));

        $cheque = $this->check;

        if (in_array($cheque->status, [Cheque::STATUS_INPROGRESS, Cheque::STATUS_SUCCESS, Cheque::STATUS_FAILED])) {
            Log::debug('Handling cloudpayments job: Duplicate job, status already sent #' . $cheque->doc_num . ': ' . $cheque->status);
            return;
        }

        $orgData = is_array($cheque->organisation->data) ? $cheque->organisation->data : [];
        $auth    = isset($orgData['cloudpayments']) ? $orgData['cloudpayments'] : null;
        $inn     = isset($orgData['inn'])            ? $orgData['inn']           : null;

        $cp = new Cloudpayments(false, $auth, $inn);

        try {
            $response = $cp->cheque($cheque);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }

        Log::debug('Sleep cloudpayments job');
        sleep(5);
    }
}

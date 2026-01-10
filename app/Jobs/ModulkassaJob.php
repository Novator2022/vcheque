<?php

namespace App\Jobs;

use Log;
use App\Cheque;
use App\Services\Modulkassa;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ModulkassaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // protected $connection = 'redis';
    protected $tries = 3;

    protected $check;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cheque $check)
    {
        $this->check = $check;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('Handing modulkassa job: ' . json_encode($this->check));

        $cheque = $this->check;

        if (in_array($cheque->status, [Cheque::STATUS_INPROGRESS, Cheque::STATUS_SUCCESS, Cheque::STATUS_FAILED])) {
            Log::debug('Handing modulkassa job: Dubplicate job status is already sent #' . $cheque->doc_num . ": " . $cheque->status);
            return;
        }

        $auth = $cheque->organisation->data["modulkassa"] ?? null;
        $mk = new Modulkassa(false, $auth);

        try {
            $response = $mk->cheque($cheque);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }

        Log::debug('Sleep modulkassa job');
        sleep(5);
    }
}

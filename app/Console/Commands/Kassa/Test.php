<?php

namespace App\Console\Commands\Kassa;

use Illuminate\Console\Command;
use App\Services\Modulkassa;
use App\Cheque;
use App\Organisation;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kassa:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $mk = new Modulkassa(false);
        foreach (Cheque::whereIn("status", ['request', 'failed'])->whereIn('organisation_id', Organisation::where('modulkassa', true)->pluck('id'))->orderBy('id', 'desc')->get() as $cheque) {
            $response  = $mk->cheque($cheque);
            $this->info('#' . $cheque->id . ' -' . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        foreach (Cheque::whereIn("status", ['inprogress', 'failed'])->whereNotNull('modulkassa')->whereIn('organisation_id', Organisation::where('modulkassa', true)->pluck('id'))->get() as $cheque) {
            $response  = $mk->chequeStatus($cheque);
            $this->info('#' . $cheque->id . ' -' .json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

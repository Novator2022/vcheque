<?php

namespace App\Console\Commands\Kassa;

use Illuminate\Console\Command;
use App\Services\Modulkassa;
use App\Cheque;
use App\Organisation;

class Checks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kassa:checks {--cheque=}';

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
        $cheques = Cheque::whereIn('organisation_id', Organisation::where('modulkassa', true)->pluck('id'));

        $cheque = $this->option('cheque');
        if (!empty($cheque)) {
            $cheques->where('id', $cheque);
        } else {
            $cheques->whereIn("status", ['inprogress'])->whereNotNull('modulkassa');
        }
        $cheques->where('created_at', '>=', now()->subDays(1)->startOfDay());

        \Log::debug('Cheques check ' . $cheques->count());

        foreach ($cheques->get() as $cheque) {
            $auth = $cheque->organisation->data["modulkassa"] ?? null;
            try {
                $mk = new Modulkassa(false, $auth);
                $response  = $mk->chequeStatus($cheque);
                $this->info('#' . $cheque->id . ' -' .json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

        }
    }
}

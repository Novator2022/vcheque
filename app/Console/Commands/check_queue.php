<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cheque;

class check_queue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cheque:check_queue';

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
        $cheques = Cheque::orderBy('id', 'desc')->limit(10)->get();
        $count = 0;
        foreach ($cheques as $cheque) {
            if (!in_array($cheque->status, ['success', 'failed'])) {
                $count++;
            }
        }

        if ($count >= 2) {
            file_put_contents(base_path('reboot_queue.lock'), 1);
            file_put_contents(base_path('storage/logs/reboot_queue.log'), date('d.m.Y H:i:s') . "\n" . 'Reboot queue - ' . $count . "\n\n", 8);
        }
    }
}

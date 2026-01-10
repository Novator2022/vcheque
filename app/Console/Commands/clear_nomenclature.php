<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cheque;
use DB;

class clear_nomenclature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cheque:clear_nomenclature';

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
        DB::table('nomenclatures')->truncate();
    }
}

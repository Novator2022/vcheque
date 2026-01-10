<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your apsplication.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cheque:schedule')
            ->cron('*/2 * * * *');

        $schedule->command('cheque:check_queue')
            ->cron('*/5 * * * *');
        
        $schedule->command('cheque:clear_nomenclature')
            ->cron('0 0 * * *');

        // $schedule->command('cheque:print_cheque')
        //     ->cron('*/5 * * * *');
            
        // $schedule->command('cheque:touch')
        //     ->hourlyAt('20')
        //     ->between('6:00', '16:00');

        // $schedule->command('kassa:checks')
        //     ->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

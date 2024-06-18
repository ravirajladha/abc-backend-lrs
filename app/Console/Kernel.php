<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Schedule the queue:restart command to run at 4 AM daily except Sundays
        $schedule->command('queue:restart')
                //  ->dailyAt('04:00')
                ->everyMinute()
                 ->when(function () {
                     return now()->isWeekday();
                 });

        // Schedule the send:dinacharya-messages command to run at 4 AM daily except Sundays
        $schedule->command('send:dinacharya-messages')
                //  ->dailyAt('04:00')
                 ->everyMinute()
                 ->when(function () {
                     return now()->isWeekday();
                 });

        // Schedule the queue:work command to run at 4 AM daily except Sundays
        $schedule->command('queue:work --daemon')
                //  ->dailyAt('04:00')
                 ->everyMinute()
                 ->when(function () {
                     return now()->isWeekday();
                 });
    }
    

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
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
        // $schedule->command('inspire')->hourly();
        $schedule->command('deleteExpire:user-refresh-token')->dailyAt('02:00');
        $schedule->command('update-state-survey')->everyTenMinutes();
        $schedule->command('push-notification-package-client')->dailyAt('06:00');
        $schedule->command('push-notification-data-storage-expires')->dailyAt('06:00');
        $schedule->command('check-project-is-almost-expires')->dailyAt('06:00');
        $schedule->command('delete-project-data-storage-expires')->dailyAt('06:00');
    }



    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

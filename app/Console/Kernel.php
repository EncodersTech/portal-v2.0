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
        if (config('app.env') == 'production') {
            // Backups are only run on the liver server.
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('02:00');
        }

        $logFile = storage_path() . '/logs/' . 'scheduler.log';

        $schedule->command('balance:clear')
            ->twiceDaily(0, 12)
            ->withoutOverlapping()
            ->sendOutputTo($logFile)
            ->emailOutputOnFailure(config('admin_email'));

        $schedule->command('withdraw-balance:clear')
            ->twiceDaily(0, 12)
            ->withoutOverlapping()
            ->sendOutputTo($logFile)
            ->emailOutputOnFailure(config('admin_email'));

        $schedule->command('usag:remind')
            ->twiceDaily(8, 20)
            ->withoutOverlapping()
            ->sendOutputTo($logFile)
            ->emailOutputOnFailure(config('admin_email'));
       
        $schedule->command('withdraw-dwolla-balance:start')
            ->everyMinute()
            ->withoutOverlapping()
            ->sendOutputTo($logFile);
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

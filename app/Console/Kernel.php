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
        $schedule->command('link:check:kc')
            ->weekdays()
            ->everyFiveMinutes()
            ->between('7:04', '7:36');

        $schedule->command('link:check:lww')
            ->weekdays()
            ->everyTenMinutes()
            ->between('6:50', '7:41');

        $schedule->command('link:check:kc')
            ->weekdays()
            ->everyFiveMinutes()
            ->between('7:05', '7:35');

        $schedule->command('link:check:lc')
            ->weekdays()
            ->at('8:05');

        $schedule->command('link:check:ew')
            ->dailyAt('21:45');

        $schedule->command('link:check:ew:compare')
            ->dailyAt('20:00');

        $schedule->command('link:check:special')
            ->dailyAt('00:01');

        $schedule->command('link:eow')
            ->fridays()
            ->at('17:30');
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

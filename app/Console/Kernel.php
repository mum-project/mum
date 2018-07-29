<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            Log::debug('Schedule started');
        });

        $schedule->command('aliases:deactivate')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        if (config('mum.system_health.check_services')) {
            $healthCommand = $schedule->command('system-services:health')
                ->withoutOverlapping();
            $this->setServiceHealthCheckFrequency($healthCommand);
        }

        if (config('mum.size_measurements.delete_old')) {
            $schedule->command('size-measurements:delete-old')
                ->withoutOverlapping()
                ->daily();
        }
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

    private function setServiceHealthCheckFrequency($scheduleCommand)
    {
        switch (config('mum.system_health.check_frequency')) {
            case '1min':
                return $scheduleCommand->everyMinute();
            case '5min':
            default:
                return $scheduleCommand->everyFiveMinutes();
            case '10min':
                return $scheduleCommand->everyTenMinutes();
            case '15min':
                return $scheduleCommand->everyFifteenMinutes();
            case '30min':
                return $scheduleCommand->everyThirtyMinutes();
        }
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notifications:create-monthly')
            ->dailyAt('00:05')
            ->withoutOverlapping();

        $schedule->command('notifications:deliver')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('bot:send-lead-follow-ups')
            ->everyTenMinutes()
            ->withoutOverlapping();

        $schedule->command('bot:request-feedback')
            ->dailyAt('11:00')
            ->withoutOverlapping();

        $schedule->command('bot:purge-files')
            ->dailyAt('02:40')
            ->withoutOverlapping();

        $schedule->command('expenses:generate-recurring')
            ->dailyAt('00:15')
            ->withoutOverlapping();

        $schedule->command('orders:send-deadline-alerts')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('finance:scan-margin-leaks')
            ->dailyAt('00:30')
            ->withoutOverlapping();

        $schedule->command('sanctum:prune-expired --hours=24')
            ->dailyAt('01:00')
            ->withoutOverlapping();

        $schedule->command('b2b:generate-invoices')
            ->monthlyOn(1, '00:20')
            ->withoutOverlapping();

        $schedule->command('backup:database --keep=7')
            ->dailyAt('01:30')
            ->withoutOverlapping();

        $schedule->command('backup:verify')
            ->monthlyOn(1, '02:10')
            ->withoutOverlapping();

        $schedule->command('security:monitor')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('security:purge-temporary-files')
            ->hourly()
            ->withoutOverlapping();
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

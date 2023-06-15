<?php

namespace App\Console;

use App\Console\Commands\AddInterestToLoansAtVariousStages;
use App\Console\Commands\MoveOverDueLoansToNextStages;
use App\Console\Commands\ProcessPendingLoans;
use App\Console\Commands\RevertHolidayToRegularDay;
use App\Console\Commands\Stage0RepaymentReminder;
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
        $schedule->command('sanctum:prune-expired --hours=24')->daily()->runInBackground();
        $schedule->command(RevertHolidayToRegularDay::class)->daily()->runInBackground();

        $schedule->command(ProcessPendingLoans::class, ['--isolated'])->everyMinute();
        $schedule->command(Stage0RepaymentReminder::class)->dailyAt('9:00');


        // These two are expected to execute sequentially in the order below ---------------
        $schedule->command(MoveOverDueLoansToNextStages::class)->daily();
        $schedule->command(AddInterestToLoansAtVariousStages::class)->daily();

        // ---

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

<?php

namespace App\Console\Commands;

use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Notifications\LoanStage0ReminderGenerated;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Stage0RepaymentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stage0-repayment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueStage0 = ConfigLoanOverdueStage::with([])->where('name', '0')->first();

        // all loans whose deadline are in 3 days time

        $loanApplicationsAtStage0 = LoanApplication::with([])
            ->where('loan_overdue_stage_id', $overdueStage0->{'id'})
            ->where('closed','=', false)
            ->whereDate('deadline', '<=', today()->addDays(3))
            ->get();

        foreach ($loanApplicationsAtStage0 as $loan) {

            $user = $loan->user;
            $name = $user->name;
            $deadline = Carbon::parse($loan->{'deadline'});
            $notificationMessage = "Hello $name. ";

            if($deadline->startOfDay()->isToday()){
                $notificationMessage .= 'Today is the last day for your loan repayment. Your loan will attract interest if its not paid before tomorrow';
            }else {
                $daysLeft = $deadline->startOfDay()->diffInDays(today());
                $notificationMessage .= "You have $daysLeft day(s) left to pay your loan. Kindly make payment to avoid any penalties";
            }

            if(blank($notificationMessage)){
               continue;
            }

            $user->notify(new LoanStage0ReminderGenerated(message: $notificationMessage));

        }

    }
}

<?php

namespace App\Console\Commands;

use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MoveOverDueLoansToNextStages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-over-due-loans-to-next-stages';

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
        $loanStages = ConfigLoanOverdueStage::with([])->where('name', '>', '0')->get();
        foreach ($loanStages as $stage) {
            // stage name  = 1 .... n

            $previousStage = ConfigLoanOverdueStage::with([])->where('name', '=',$stage->name - 1)->first();
            if(blank($previousStage)){
                continue;
            }

            //Get all loans whose deadline has exceeded between x to y days
            //and are still on the previous stage

            $previousStageId = $previousStage->{'id'};
            $xDaysAfterDeadline = $stage->{'from_days_after_deadline'} ?: 0;
            $yDaysAfterDeadline = $stage->{'to_days_after_deadline'} ?: Carbon::now()->addCentury()->diffInDays(now());


            $loans = LoanApplication::with([])
                ->where('loan_overdue_stage_id', '=', $previousStageId)
                ->where('closed','=', false)
                ->get();

            foreach ($loans as $loan) {

                $deadline = Carbon::parse($loan->{'deadline'})->startOfDay();

                // if the deadline is not yet due
                if($deadline->greaterThanOrEqualTo(today())){
                    continue;
                }

                // all deadline which are overDue
                $diffInDaysBetweenDeadlineAndToday = $deadline->diffInDays(today());
                if($diffInDaysBetweenDeadlineAndToday >= $xDaysAfterDeadline && $diffInDaysBetweenDeadlineAndToday <= $yDaysAfterDeadline){

                    // moved to stage $stage
                    $loan->update([
                        'loan_overdue_stage_id' => $stage->{'id'},
                        'assigned_to' => null // once the loan is moved to the next stage assigned to is null
                    ]);
                    LoanApplicationStatus::with([])->create([
                        'loan_application_id' => $loan->{'id'},
                        'status' => 'stage-' . $stage->{'name'},
                        'user_id' => null,
                        'created_by' => 'system'
                    ]);

                }


            }

            // get all loans in the previous stage whose deadline is due ----

        }
    }
}

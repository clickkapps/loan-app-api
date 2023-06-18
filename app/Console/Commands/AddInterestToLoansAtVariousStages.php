<?php

namespace App\Console\Commands;

use App\Models\ConfigLoanOverdueStage;
use App\Models\Configuration;
use App\Models\LoanApplication;
use Illuminate\Console\Command;

class AddInterestToLoansAtVariousStages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-interest-to-loans-at-various-stages';

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

        // check if interests have paused ....
        $config = Configuration::with([])->first();
        // if all interests pause, then we don't have to accumulate interest
        if($config->{'pause_all_interests'}){
            return;
        }

        $loanStages = ConfigLoanOverdueStage::with([])->where('name', '>', '0')->get();
        foreach ($loanStages as $stage) {

            $interestPercentagePerDay = $stage->{'interest_percentage_per_day'};

            $loans = LoanApplication::with([])->where('loan_overdue_stage_id', '=', $stage->{'id'})->get();
            foreach ($loans as $loan) {

                $interestWavedPercentage = $loan->{'wave_interest_by_percentage'};

                $amountDisbursed =  $loan->{'amount_disbursed'};
                $interest = $amountDisbursed * ($interestPercentagePerDay / 100);
                $interestWaved = ($interestWavedPercentage / 100) * $interest;

                $interestToPay = $interest - $interestWaved;


                $loan->update([
                    'accumulated_interest' => $loan->{'accumulated_interest'} + $interestToPay,
                    'amount_to_pay' => $loan->{'amount_to_pay'} + $interestToPay
                ]);
            }
        }
    }
}

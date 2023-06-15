<?php

namespace App\Traits;

use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait CommissionTrait
{
    public  function creditAgentBaseOnLoanRepayment($userId, $amountPaid, $loan): void
    {
        // get the loan stage, and
        // check if today is holday and find the percentage given on holidays for that loan stage
        // else check if today is weekday and find percentage for that loan stage
        // else check if today is weekend and find percentage for that loan stage

        Log::info("attempting to credit agent based of repayment ----------");

        $stage = $loan->{'stage'};
        $stageName = $stage->{'name'};
        $agentUser = User::with([])->find($userId);
        if(blank($agentUser)){
            Log::info("Invalid agent UserId ...");
            return;
        }
        $agent = $agentUser->{'agent'};
        if(blank($agent)){
            Log::info("User is not an agent ......");
            return;
        }
        $commissionConfig = $stage->{'commission'};
        if(blank($commissionConfig)){
            Log::info("Commissions not configured for loan stage $stageName");
            return;
        }

        $todayDesc = getTodayDescription();
        $perc = 0;
        if($todayDesc == 'holiday'){
            $perc = $commissionConfig->{'percentage_on_repayment_holidays'};
        }else if($todayDesc == 'weekday') {
            $perc = $commissionConfig->{'percentage_on_repayment_weekdays'};
        }else if($todayDesc == 'weekend') {
            $perc = $commissionConfig->{'percentage_on_repayment_weekends'};
        }

        $commission = $perc / 100 * $amountPaid;
        $agent->update([
            'balance' => $agent->balance + $commission
        ]);

        Log::info("Agent commission credited successfully ...");
        $agent->refresh();
        Log::info(json_encode($agent));


    }


    public  function creditAgentBaseOnLoanDeferment($userId, $amountPaid, $loan): void
    {
        // get the loan stage, and
        // check if today is holday and find the percentage given on holidays for that loan stage
        // else check if today is weekday and find percentage for that loan stage
        // else check if today is weekend and find percentage for that loan stage

        Log::info("attempting to credit agent based of deferment ----------");

        $stage = $loan->{'stage'};
        $stageName = $stage->{'name'};
        $agentUser = User::with([])->find($userId);
        if(blank($agentUser)){
            Log::info("Invalid agent UserId ...");
            return;
        }
        $agent = $agentUser->{'agent'};
        if(blank($agent)){
            Log::info("User is not an agent ......");
            return;
        }
        $commissionConfig = $stage->{'commission'};
        if(blank($commissionConfig)){
            Log::info("Commissions not configured for loan stage $stageName");
            return;
        }

        $todayDesc = getTodayDescription();
        $perc = 0;
        if($todayDesc == 'holiday'){
            $perc = $commissionConfig->{'percentage_on_deferment_holidays'};
        }else if($todayDesc == 'weekday') {
            $perc = $commissionConfig->{'percentage_on_deferment_weekdays'};
        }else if($todayDesc == 'weekend') {
            $perc = $commissionConfig->{'percentage_on_deferment_weekends'};
        }

        $commission = $perc / 100 * $amountPaid;
        $agent->update([
            'balance' => $agent->balance + $commission
        ]);

        Log::info("Agent commission credited successfully ...");
        $agent->refresh();
        Log::info(json_encode($agent));


    }


    // userid is the agent userit
    public  function debitAgent($userId, $amount) {

    }
}

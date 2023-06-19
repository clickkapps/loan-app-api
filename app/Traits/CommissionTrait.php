<?php

namespace App\Traits;

use App\Models\AgentTask;
use App\Models\Commission;
use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait CommissionTrait
{
    public  function creditAgentBaseOnLoanRepayment($userId, $amountPaid, $loan, bool $isPartPayment): void
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

        $commission = $perc; // flat rate for full repayment

        // for partial repayment
        // if the customer makes part payment, the commission will be a fraction of the laid down commission
        if($isPartPayment && $loan->{'amount_to_pay'} > 0) {
            $percentageOfAmountPaid = $amountPaid / $loan->{'amount_to_pay'} * 100;
            $commission = ($percentageOfAmountPaid / 100) * $commission;
        }
//        $commission = $perc / 100 * $amountPaid;


        Commission::with([])->create([
            'user_id' => $userId,
            'loan_id' => $loan->{'id'},
            'amount' => $commission,
            'action' => $isPartPayment ? 'part-repayment' : 'full-repayment', //'part-payment, full-payment', 'deferment'
            'creator_id' => $loan->{'user_id'}
        ]);

//        add to the agentTasks if its full repayment
        if(!$isPartPayment){
            $existingTask = AgentTask::with([])->where(['user_id' => $userId, 'date' => Carbon::today()])->first();
            AgentTask::with([])->where(['user_id' => $userId, 'date' => Carbon::today()])
                ->update([
                    'collected_count' => $existingTask->{'collected_count'} + 1,
                    'collected_amount' => $existingTask->{'collected_amount'} + $amountPaid,
                ]);
        }

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

//        $commission = $perc / 100 * $amountPaid;
        $commission = $perc; // flat rate
//        $agent->update([
//            'balance' => $agent->balance + $commission
//        ]);

        Commission::with([])->create([
            'user_id' => $userId,
            'loan_id' => $loan->{'id'},
            'amount' => $commission,
            'action' => 'deferment', //'full-payment, part-payment, deferment'
            'creator_id' => $loan->{'user_id'}
        ]);

        //        add to the agentTasks
        $existingTask = AgentTask::with([])->where(['user_id' => $userId, 'date' => Carbon::today()])->first();
        AgentTask::with([])->where(['user_id' => $userId, 'date' => Carbon::today()])
            ->update([
                'collected_count' => $existingTask->{'collected_count'} + 1,
                'collected_amount' => $existingTask->{'collected_amount'} + $amountPaid,
            ]);

        Log::info("Agent commission credited successfully ...");
        $agent->refresh();
        Log::info(json_encode($agent));


    }


}

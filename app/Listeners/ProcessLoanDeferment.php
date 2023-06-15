<?php

namespace App\Listeners;

use App\Events\PaymentCallbackReceived;
use App\Models\ConfigLoanOverdueStage;
use App\Models\Configuration;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Notifications\DefermentReceived;
use App\Notifications\RepaymentReceived;
use App\Traits\CommissionTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLoanDeferment
{
    use CommissionTrait;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCallbackReceived $event): void
    {
        $payment = $event->payment;
        if($payment->{'description'} != 'Loan deferment') {
            return;
        }

        $loan = LoanApplication::with([])->where('id','=', $payment->{'loan_application_id'})->first();
        $user = $loan->user;

        // we are only interest in successful payments
        if($payment->{'response_code'} == '200' || $payment->{'response_code'} == '201') {

            // add status that loan moved to stage 0
            $loanStageAt0 = ConfigLoanOverdueStage::with([])->where("name",'=','0')->first();
            $generalConfig = Configuration::with([])->first();
            $durationLimit = $generalConfig->{'loan_application_duration_limit'};

            $amountPaid = $payment->{'amount'};
            $amountRemaining = $loan->{'amount_to_pay'} - $amountPaid;

            LoanApplicationStatus::with([])->create([
                'loan_application_id' => $payment->{'loan_application_id'},
                'status' => 'deferred',
                'user_id' => $payment->{'created_by_user_id'},
                'created_by' => $payment->{'created_by_name'}
            ]);

            $loan->update([
                'loan_overdue_stage_id' => $loanStageAt0->{'id'},
                'amount_to_pay' => $amountRemaining,
                'amount_disbursed' => $loan->{'amount_requested'},
                'deadline'  => now()->addDays($durationLimit)
            ]);

            // credit tha agent assigned to this loan
            if($loan->{'assigned_to'}) {
                $this->creditAgentBaseOnLoanDeferment(userId: $loan->{'assigned_to'}, amountPaid: $amountPaid, loan: $loan);
            }

            $user->notify(new DefermentReceived(amount: $amountPaid));
        }

        // over here,  we don't really care if the payment failed
    }
}

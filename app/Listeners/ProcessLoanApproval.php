<?php

namespace App\Listeners;

use App\Events\PaymentCallbackReceived;
use App\Events\PaymentStatusReceived;
use App\Models\ConfigLoanOverdueStage;
use App\Models\Configuration;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Notifications\LoanMovedToStage0;
use App\Traits\LoanApplicationTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLoanApproval
{
    use LoanApplicationTrait;
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
    public function handle(PaymentCallbackReceived $event)
    {
        // update the loan status, that process is successful
        $payment = $event->payment;
        $loan = LoanApplication::with([])->where('id','=', $payment->{'loan_application_id'})->first();
        $user = $loan->user;

        if($payment->{'response_code'} == '200' || $payment->{'response_code'} == '201') {
            // loan disbursal successful

            // add status that loan moved to stage 0
            $loanStageAt0 = ConfigLoanOverdueStage::with([])->where("name",'=','0')->first();
            $generalConfig = Configuration::with([])->first();
            $durationLimit = $generalConfig->{'loan_application_duration_limit'};

            LoanApplicationStatus::with([])->create([
                'loan_application_id' => $payment->{'loan_application_id'},
                'status' => 'stage-0',
                'user_id' => $payment->{'created_by_user_id'},
                'created_by' => $payment->{'created_by_name'}
            ]);

            $loan->update([
                    'loan_overdue_stage_id' => $loanStageAt0->{'id'},
                    'amount_to_pay' => $loan->{'amount_requested'},
                    'amount_disbursed' => $loan->{'amount_requested'},
                    'deadline' => now()->addDays($durationLimit)
            ]);

            // send notification that loan disbursal is successful

            $user->notify(new LoanMovedToStage0(loan: $loan, status: 'success', desc: 'loan request'));

        }else {

            // loan disbursal failed

            // add status that loan process failed
            LoanApplicationStatus::with([])->create([
                'loan_application_id' => $payment->{'loan_application_id'},
                'status' => 'stage-0-failed',
                'user_id' => $payment->{'created_by_user_id'},
                'created_by' => $payment->{'created_by_name'}
            ]);

            $user->notify(new LoanMovedToStage0(loan: $loan, status: 'failed', desc: 'loan request'));
        }

        // unlock related loan
        LoanApplication::with([])->where('id', '=', $payment->{'loan_application_id'})
            ->update([
                'locked' => false
            ]);

        return response()->json(['message' => 'received'], 200);

    }
}

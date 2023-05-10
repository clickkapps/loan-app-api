<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\LoanApplication;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentInitiated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait LoanApplicationTrait
{

    public function getLoanApplicationUpdate(Request $request): \Illuminate\Http\JsonResponse
    {

        $runningLoan = LoanApplication::with([])->where('closed',false)->first();
        if(blank($runningLoan)){
            return response()->json(ApiResponse::successResponseWithData(
                [
                    'loan' => null,
                ]
            ));
        }

        $applicationStatus = $runningLoan->latestStatus;
        $displayStatus = $applicationStatus->status;
        if($displayStatus == "requested") {
            $displayStatus = "Pending approval";
        }else if($displayStatus == "disbursed") {
            $displayStatus = "Loan disbursed";
        }else if($displayStatus == "denied") {
            $displayStatus = "Application denied";
        }

        return response()->json(ApiResponse::successResponseWithData(
            [
                'loan' => $runningLoan,
                'display_status' => $displayStatus,
                'status' => $applicationStatus->status
            ]
        ));


    }

    public function getLoansWhoseLatestStatusIs(string $status): \Illuminate\Database\Eloquent\Collection|array
    {
        return LoanApplication::with(['latestStatus'])->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('loan_application_statuses')
                ->groupBy('loan_application_id');
        })
            ->whereHas('statuses', function ($query) {
                $query->where('status', '=', 'requested');
            })
            ->get();
    }

    public function initiateLoanDisbursal(LoanApplication $loan): void
    {
        // lock the loan application so that it can't be acted on again
        if($loan->{'locked'}) {
            return;
        }

        $loan->update([
            'locked' => true
        ]);


        $userId = $loan->{'user_id'};
        $amount = $loan->{'amount'};
        $accountNumber = $loan->{'account_number'};
        $accountName = $loan->{'account_name'};
        $networkType = $loan->{'network_type'};
        $title = config('app.name');
        $description = 'Loan disbursal';
        $loanId = $loan->{'id'};

        // unique reference number ------
        $date = now()->toDateTimeString();
        $date = str_replace('-','', $date);
        $date = str_replace(':','', $date);

        $clientRef = $loanId . $date . generateRandomNumber();
        Log::info("client ref: $clientRef");
        //---------------------------------------------------------


        // record the transaction
        $payment = Payment::with([])->create([
            'user_id' => $userId,
            'loan_application_id' => $loanId,
            'client_ref' => $clientRef,
//            'server_ref',
            'amount' => $amount,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'network_type' => $networkType,
            'title' => $title,
            'description' => $description,
//            'response_message',
//            'response_code',
            'status' => 'opened',
//            'extra'
        ]);

        /// Call PAYMENT GATEWAY API to make payment
        ///

        $user = User::find($userId);
        $user->notify(new PaymentInitiated(payment: $payment));


    }


    public function paymentCallback(Request $request): void
    {

        $responseCode = $request->get('responseCode');

//         let' assume its successful --------
        if($responseCode == '200' || $responseCode == '201') {

            

        }

        else {

            //     if it fails --------

        }



    }
}

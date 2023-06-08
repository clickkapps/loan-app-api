<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\ConfigLoanOverdueStage;
use App\Models\FollowUp;
use App\Models\LoanApplication;
use App\Models\LoanAssignedTo;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentInitiated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait LoanApplicationTrait
{

    public function getLoanApplicationUpdate(Request $request): \Illuminate\Http\JsonResponse
    {

        $runningLoan = LoanApplication::with([])->where([
            'closed' => false,
            'user_id' => $request->user()->id
        ])->first();
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
        }else if($displayStatus == "denied") {
            $displayStatus = "Application denied";
        }else if($displayStatus == "stage-0") {
            $displayStatus = "Loan disbursed";
        }

        return response()->json(ApiResponse::successResponseWithData(
            [
                'loan' => $runningLoan,
                'display_status' => $displayStatus,
                'status' => $applicationStatus->status
            ]
        ));


    }

    public function getLoansWhoseLatestStatusIs(string $status)
    {

        return LoanApplication::with(['latestStatus'])->latestStatusName($status)->get();
    }

    public function initiateLoanDisbursal(LoanApplication $loan, string $createdByName, User $createdByUser = null): void
    {
        // lock the loan application so that it can't be acted on again
        if($loan->{'locked'}) {
            return;
        }

        $loan->update([
            'locked' => true
        ]);


        $userId = $loan->{'user_id'};
        $amount = $loan->{'amount_requested'} - $loan->{'fee_charged'};
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
        $date = str_replace(' ','', $date);

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
            'created_by_name' => $createdByName,
            'created_by_user_id' => $createdByUser?->{'id'},
//            'extra'
        ]);

        /// Call PAYMENT GATEWAY API to make payment
        ///

        $user = User::find($userId);
        $user->notify(new PaymentInitiated(payment: $payment));


    }


    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function assignLoanToAgent(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_id' => 'required',
            'user_id' => 'required',
        ]);

        $loanId = $request->get('loan_id');
        $userId = $request->get('user_id');

        $loan = LoanApplication::with([])->find($loanId);

        if(blank($loan)) {
            throw new \Exception("Loan does not exists");
        }

        // check if loan is already assigned to a user
        if(!blank($loan->{'assigned_to'})) {
            throw new \Exception("Loan has already been assigned to an agent");
        }

        $loan->update([
            'assigned_to' => $userId
        ]);

        // can be assigned to
        LoanAssignedTo::with([])->updateOrCreate([
            'loan_application_id' => $loanId,
            'user_id' => $userId,
            'stage_id' => $loan->{'loan_overdue_stage_id'}
        ], [] );

        // notify all apps that this loan is no longer available
        \Illuminate\Support\Facades\Log::info('LoanApplicationAssignedToAgent push-event called:');
        \Illuminate\Support\Facades\Log::info(json_encode($loan));
        event(new \App\Events\LoanApplicationAssignedToAgent(loanApplication: $loan));


        return response()->json(ApiResponse::successResponseWithData());

    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function unAssignLoanToAgent(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_id' => 'required',
        ]);

        $loanId = $request->get('loan_id');

        $loan = LoanApplication::with([])->find($loanId);

        if(blank($loan)) {
            throw new \Exception("Loan does not exists");
        }

        if(blank($loan->{'assigned_to'})) {
            throw new \Exception("Loan has not been assigned to any agent");
        }

        $loan->update([
            'assigned_to' => null
        ]);

        return response()->json(ApiResponse::successResponseWithData());

    }

    public function getAssignedLoans($userId): \Illuminate\Http\JsonResponse
    {
        $loans = LoanApplication::with([])->where([
            'assigned_to' => $userId
        ]);

        return response()->json(ApiResponse::successResponseWithData($loans));
    }

    public function getLoanStagesFromPermissions(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = $request->user();
        $permissionNames = $user->getPermissionNames();
        $stages = collect($permissionNames)->filter(function ($item) {
            return str_contains($item, 'access to loan stage');
        })->map(function ($item){
            return substr($item, -1);
        });

        $loanStages = ConfigLoanOverdueStage::with([])->whereIn('name', $stages)->get();
        return response()->json(ApiResponse::successResponseWithData($loanStages));

    }

    public function getFollowUpRecords(Request $request, $loanId): \Illuminate\Http\JsonResponse
    {

        $query = FollowUp::with(['agent', 'loan'])
            ->where(['loan_application_id' => $loanId]);

        if(!blank($request->get('userId'))){
            $query->where(['agent_user_id' => $request->get('userId')]);
        }

        $records = $query->get();

        return response()->json(ApiResponse::successResponseWithData($records));

    }



}

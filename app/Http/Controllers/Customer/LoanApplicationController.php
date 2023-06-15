<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigLoanOverdueStage;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\DefermentInitiated;
use App\Notifications\PaymentInitiated;
use App\Notifications\RepaymentInitiated;
use App\Traits\LoanApplicationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoanApplicationController extends Controller
{
    use LoanApplicationTrait;

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function submitApplication(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'amount' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'network_type' => 'required'
        ]);

        $user = $request->user();

        $amountRequested = $request->get('amount');
        $accountNumber = $request->get('account_number');
        $accountName = $request->get('account_name');
        $networkType = $request->get('network_type');

        // user cannot apply for another loan if a loan is already running
        $runningLoan = LoanApplication::with([])->where(
            [
                'closed' => false,
                'user_id' => $request->user()->id
            ])->exists();
        if($runningLoan) {
            throw new \Exception('You have an existing running loan. Kindly contact support to close any previous loan applications');
        }

        $config =  Configuration::with([])->first();
        $loanState = ConfigLoanOverdueStage::with([])->where([
            'key' => 'not_overdue'
        ])->first();
        $customer = Customer::with([])->where('user_id', '=', $user->id)->first();

        $fee = $amountRequested * $config->{'processing_fee_percentage'} / 100 + $amountRequested * $config->{'loan_application_interest_percentage'} / 100 ;

       $amountLimit  = $customer->{'loan_application_amount_limit'} ?: $config->{'loan_application_amount_limit'};
       if($amountRequested > $amountLimit) {
           throw new \Exception("Loan amount must not exceed $amountLimit");
       }

       // submit application
        $application = LoanApplication::with([])->create([
            'user_id' => $user->id,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'network_type' => $networkType,
//            'deadline' => Carbon:: // field will be created at the time of disbursal
            'amount_requested' => $amountRequested,
            'amount_disbursed' => 0.00,
            'fee_charged' => $fee,
            'amount_to_pay' => 0.0,
//            'loan_overdue_stage_id' => $loanState->{'id'}, // Stage 1 field will be created when loan is disbursed
            'closed' =>  false
        ]);

       // record application status
       LoanApplicationStatus::with([])->create([
           'loan_application_id' => $application->{'id'},
           'status' => 'requested',
           'user_id' => $user->id,
           'created_by' => 'customer'
       ]);

       return $this->fetchLoanApplicationUpdate($request);


    }

    public function fetchLoanApplicationUpdate(Request $request): \Illuminate\Http\JsonResponse
    {

        $runningLoan = $this->getLoanApplicationUpdate($request)->getData()->extra;

        return response()->json(ApiResponse::successResponseWithData([
            "running_loan" => $runningLoan
        ]));
    }

    /**
     * @throws \Exception
     */
    public function getLoanRepaymentData($loanId): \Illuminate\Http\JsonResponse
    {

        $loan = LoanApplication::with([])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan has been removed. Contact support team');
        }

        // check if loan has been approved
        if($loan->{'latestStatus'}->status == 'requested') {
            throw new \Exception("Loan is pending approval");
        }

        // check if installments can be paid on the loan
        $loanStage = $loan->{'stage'};
        $installmentEnabled = $loanStage->{'installment_enabled'} == 1;

        return response()->json(ApiResponse::successResponseWithData([
            'installment_enabled' => $installmentEnabled,
            'amount_to_pay' => $loan->{'amount_to_pay'},
        ]));

    }

    /**
     * @throws \Exception
     */
    public function getLoanDefermentData($loanId): \Illuminate\Http\JsonResponse
    {

        $loan = LoanApplication::with([])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan has been removed. Contact support team');
        }

        // check if loan has been approved
        if($loan->{'latestStatus'}->status == 'requested') {
            throw new \Exception("Loan is pending approval");
        }

        $config = Configuration::with([])->first();
        $defermentPercentage = $config->{'deferment_percentage'};
        $defermentAmount = ($defermentPercentage / 100) * $loan->{'amount_to_pay'};

        return response()->json(ApiResponse::successResponseWithData([
            'deferment_amount' => $defermentAmount
        ]));


    }

    /**
     * @throws \Exception
     */
    public function getLoanDetails($loanId): \Illuminate\Http\JsonResponse
    {

        $loan = LoanApplication::with(['statuses'])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan has been removed. Contact support team');
        }

        $loan->{'modified_statuses'} = collect($loan->{'statuses'})->map(function ($status) {
            $display = $status->status;
            if($status->status == "requested") {
                $display = 'Loan application submitted';
            }else if($status->status == "stage-0") {
                $display = 'Loan disbursed';
            }
            return [
                'status' => $status->status,
                'display' => $display,
                'created_at' => Carbon::parse($status->{'created_at'})->toDayDateTimeString()
            ];
        });

        return response()->json(ApiResponse::successResponseWithData($loan));

    }

    public function fetchLoanApplicationHistory(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = $request->user();
        $loanData = LoanApplication::with(['latestStatus', 'stage'])
            ->where('user_id', $user->id)->orderByDesc('created_at')->paginate(30);

        return response()->json(ApiResponse::successResponseWithData($loanData));

    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function initiateLoanRepayment(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_id' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'network_type' => 'required',
        ]);

        $user = $request->user();

        $loanId = $request->get('loan_id');
        $accountNumber = $request->get('account_number');
        $accountName = $request->get('account_name');
        $networkType = $request->get('network_type');


        $loan = LoanApplication::with([])->find($loanId);
        if(blank($loan)){
            Log::info('initiateLoanRepayment: invalid loanId : request: ' . json_encode($request->all()));
            throw  new \Exception('Invalid request. contact technical team');
        }

        /// cache repayment info for next payment
        $customer = Customer::with([])->where('user_id', '=', $user->id)->first();
        $customer->update([
            'default_momo_account_number' => $accountNumber,
            'default_momo_account_name' => $accountName,
            'default_momo_network' => $networkType
        ]);

        /// initiate payment
        // unique reference number ------
        $date = now()->toDateTimeString();
        $date = str_replace('-','', $date);
        $date = str_replace(':','', $date);
        $date = str_replace(' ','', $date);

        $clientRef = $loanId . $date . generateRandomNumber();
        Log::info("client ref: $clientRef");
        //---------------------------------------------------------

        $description = 'Loan repayment';

        // if record already exists for the loan, you don't have to create the record again
        $payment = Payment::with([])->where([
            'description' => $description,
            'loan_application_id' => $loanId
        ])->first();

        if(!blank($payment)) {
            throw new \Exception("Duplicate repayment request detected on the given loan");
        }

        // record the transaction
        $payment = Payment::with([])->create([

            'user_id' => $user->id,
            'loan_application_id' => $loanId,
            'client_ref' => $clientRef,
//            'server_ref',
            'amount' => $loan->{'amount_to_pay'},
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'network_type' => $networkType,
            'title' => config('app.name'),
            'description' => $description,
//            'response_message',
//            'response_code',
            'status' => 'opened',
            'created_by_name' => 'user',
            'created_by_user_id' => $user->id,

        ]);

        // submit to payment gateway
        $user = User::find($user->id);
        $user->notify(new RepaymentInitiated(payment: $payment));

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function initiateLoanDeferment(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_id' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'network_type' => 'required',
        ]);

        $user = $request->user();

        $loanId = $request->get('loan_id');
        $accountNumber = $request->get('account_number');
        $accountName = $request->get('account_name');
        $networkType = $request->get('network_type');


        $loan = LoanApplication::with([])->find($loanId);
        if(blank($loan)){
            Log::info('initiateLoanDeferment: invalid loanId : request: ' . json_encode($request->all()));
            throw  new \Exception('Invalid request. contact technical team');
        }

        /// cache repayment info for next payment
        $customer = Customer::with([])->where('user_id', '=', $user->id)->first();
        $customer->update([
            'default_momo_account_number' => $accountNumber,
            'default_momo_account_name' => $accountName,
            'default_momo_network' => $networkType
        ]);

        /// initiate payment
        // unique reference number ------
        $date = now()->toDateTimeString();
        $date = str_replace('-','', $date);
        $date = str_replace(':','', $date);
        $date = str_replace(' ','', $date);

        $clientRef = $loanId . $date . generateRandomNumber();
        Log::info("client ref: $clientRef");
        //---------------------------------------------------------

        $description = 'Loan deferment';

        // if record already exists for the loan, you don't have to create the record again
        $payment = Payment::with([])->where([
            'description' => $description,
            'loan_application_id' => $loanId,
            'status' => 'opened'
        ])->first();

        if(!blank($payment)) {
            throw new \Exception("Duplicate deferment request detected on the given loan");
        }

        $config = Configuration::with([])->first();
        $defermentPercentage = $config->{'deferment_percentage'};
        $defermentAmount = ($defermentPercentage / 100) * $loan->{'amount_to_pay'};

        // record the transaction
        $payment = Payment::with([])->create([

            'user_id' => $user->id,
            'loan_application_id' => $loanId,
            'client_ref' => $clientRef,
//            'server_ref',
            'amount' => $defermentAmount,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'network_type' => $networkType,
            'title' => config('app.name'),
            'description' => $description,
//            'response_message',
//            'response_code',
            'status' => 'opened',
            'created_by_name' => 'user',
            'created_by_user_id' => $user->id,

        ]);

        // submit to payment gateway
        $user = User::find($user->id);
        $user->notify(new DefermentInitiated(payment: $payment));


        return response()->json(ApiResponse::successResponseWithMessage());

    }

}

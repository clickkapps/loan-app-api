<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\CallLog;
use App\Models\ConfigLoanOverdueStage;
use App\Models\FollowUp;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Models\LoanAssignedTo;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentInitiated;
use Carbon\Carbon;
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
        }else if($displayStatus == "assigned-to-agent") {
            $displayStatus = "Assigned to agent";
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

        return LoanApplication::with(['latestStatus', 'assignedTo'])->latestStatusName($status)->get();
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

        $authUser = $request->user();
        $permissionNames = $authUser->getRoleNames()->toArray();

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

        LoanApplicationStatus::with([])->create([
            'loan_application_id' => $loan->{'id'},
            'status' => 'assigned-to-agent',
            'user_id' => $authUser->{'id'},
            'created_by' => in_array('agent', $permissionNames) ? 'agent' : 'admin',
            'agent_user_id' => $userId,
        ]);

        $existingTaskCreatedForToday = AgentTask::with([])->where([
            'user_id' => $userId,
            'date' => Carbon::today()
        ])->first();

        $existingTaskCreatedForToday->update([
            'tasks_count' => $existingTaskCreatedForToday->{'tasks_count'} + 1,
            'tasks_amount' => $existingTaskCreatedForToday->{'tasks_amount'} + $loan->{'amount_to_pay'}
        ]);

        // can be assigned to
        LoanAssignedTo::with([])->updateOrCreate([
            'loan_application_id' => $loanId,
            'user_id' => $userId,
            'stage_id' => $loan->{'loan_overdue_stage_id'}
        ], [] );

        // agent's tasks


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
    public function assignBulkLoansToAgent(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_ids' => 'required|array',
            'user_id' => 'required',
            'stage_id' => 'required'
        ]);

        $loanIds = $request->get('loan_ids');
        $userId = $request->get('user_id');
        $stageId = $request->get('stage_id');
        $authUser = $request->user();
        $permissionNames = $authUser->getRoleNames()->toArray();

//        $loan = LoanApplication::with([])->find($loanId);
//
//        if(blank($loan)) {
//            throw new \Exception("Loan does not exists");
//        }

//        // check if loan is already assigned to a user
//        if(!blank($loan->{'assigned_to'})) {
//            throw new \Exception("Loan has already been assigned to an agent");
//        }

        LoanApplication::with([])->whereIn('id', $loanIds)->update([
            'assigned_to' => $userId
        ]);

        $dataToInsertInLoanAssignedTo = [];
        $dataToInsertInLoanStatus = [];


        $totalAmountToPay = 0.00;
        foreach ($loanIds as $loanId) {

            $amountToPay = LoanApplication::with([])->find($loanId)->{'amount_to_pay'};
            $totalAmountToPay = $totalAmountToPay + $amountToPay;

            $dataToInsertInLoanAssignedTo[] = [
                'loan_application_id' => $loanId,
                'user_id' => $userId,
                'stage_id' => $stageId
            ];

            $dataToInsertInLoanStatus[] = [
                'loan_application_id' => $loanId,
                'status' => 'assigned-to-agent',
                'user_id' => $authUser->{'id'},
                'created_by' => in_array('agent', $permissionNames) ? 'agent' : 'admin',
                'agent_user_id' => $userId,
            ];
        }

        $existingTaskCreatedForToday = AgentTask::with([])->where([
            'user_id' => $userId,
            'date' => Carbon::today()
        ])->first();

        $existingTaskCreatedForToday->update([
            'tasks_count' => $existingTaskCreatedForToday->{'tasks_count'} + count($loanIds),
            'tasks_amount' => $existingTaskCreatedForToday->{'tasks_amount'} + $totalAmountToPay
        ]);


        LoanApplicationStatus::with([])->insert($dataToInsertInLoanStatus);

        // can be assigned to
        LoanAssignedTo::with([])->upsert($dataToInsertInLoanAssignedTo, [] );

//        // notify all apps that this loan is no longer available
//        \Illuminate\Support\Facades\Log::info('LoanApplicationAssignedToAgent push-event called:');
//        \Illuminate\Support\Facades\Log::info(json_encode($loan));
        event(new \App\Events\LoanApplicationAssignedToAgent(loanApplication: null));


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
        $authUser = $request->user();
        $loan = LoanApplication::with([])->find($loanId);

        if(blank($loan)) {
            throw new \Exception("Loan does not exists");
        }

        if(blank($loan->{'assigned_to'})) {
            throw new \Exception("Loan has not been assigned to any agent");
        }

        $agentUserId = $loan->{'assigned_to'};

        // subtract the task from the agent's tasks ------
        $existingTaskCreatedForToday = AgentTask::with([])->where([
            'user_id' => $agentUserId,
            'date' => Carbon::today()
        ])->first();

        // if we don't do this check, the collection amount will be greater than the task amount for that day
        // so later on , when we're auditing we'd be wondering why for that day, his collection amount was greater that  tasks amount
        if($existingTaskCreatedForToday->{'collected_count'} > 0){
            $agentName = $existingTaskCreatedForToday->user->name;
            throw new \Exception("$agentName (already assigned agent) has started making collections for today hence loan cannot be unassigned");
        }

        $existingTaskCreatedForToday->update([
            'tasks_count' => $existingTaskCreatedForToday->{'tasks_count'} - 1,
            'tasks_amount' => $existingTaskCreatedForToday->{'tasks_amount'} - $loan->{'amount_to_pay'}
        ]);

        $loan->update([
            'assigned_to' => null
        ]);

        LoanApplicationStatus::with([])->create([
            'loan_application_id' => $loan->{'id'},
            'status' => 'un-assigned-from-agent',
            'user_id' => $authUser->{'id'},
            'created_by' => in_array('agent', $authUser->getPermissionNames()) ? 'agent' : 'admin',
            'agent_user_id' => $agentUserId,
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

        $loanStages = ConfigLoanOverdueStage::with(['commission'])->whereIn('name', $stages)->get();
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

    public function getCustomerCallLogs($userId): \Illuminate\Http\JsonResponse
    {

        $callLogs = CallLog::with([])->where(['user_id' => $userId])->orderByDesc('duration')->get();
        return response()->json(ApiResponse::successResponseWithData($callLogs));

    }





}

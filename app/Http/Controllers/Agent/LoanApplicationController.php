<?php

namespace App\Http\Controllers\Agent;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\ConfigLoanOverdueStage;
use App\Models\FollowUp;
use App\Models\FollowUpCallLog;
use App\Models\FollowUpSmsLog;
use App\Models\FollowUpWhatsappLog;
use App\Models\LoanApplication;
use App\Notifications\FollowUpSmsLogCreated;
use App\Traits\CusboardingPageTrait;
use App\Traits\LoanApplicationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoanApplicationController extends Controller
{
    use LoanApplicationTrait, CusboardingPageTrait;

    /**
     * @throws \Exception
     */
    public function getLoanApplicationsFromMyPermissions(Request $request): \Illuminate\Http\JsonResponse
    {

        // request parameters
//        status (optional): [assigned, unassigned]
//        stage (optional): stage-0, etc

        $user = $request->user();
        $query = LoanApplication::with(['latestStatus', 'assignedTo', 'user'])
            ->where('closed','=', false)
            ->where(function ($query) use ($user, $request) {
                if($request->has('status')){
                    if($request->get('status') == 'unassigned') {
                        $query->where('assigned_to', null);
                        return;
                    }
                    $query->where('assigned_to', $user->id);
                    return;
                }

                $query->where('assigned_to', '=', $user->id)
                    ->orWhere('assigned_to', null);

            });

        if($request->has('stage')){

            // pending can not be part of type

            $type = $request->get('stage');

            if($type == 'pending' || $type == 'requested'){
                throw new \Exception('Agents do not have access to pending applications from the mobile app');
            }
            $items = explode('-', $type);
            if(count($items) != 2){
                throw new \Exception('Invalid loan stage specified');
            }
            $stageName = $items[1];

            $loanStage = ConfigLoanOverdueStage::with([])->where('name', $stageName)->first();

            $query
                ->where('loan_overdue_stage_id', $loanStage->{'id'});

        }else {

            $myLoanStages = $this->getLoanStagesFromPermissions($request)->getData()->extra;

            // Get all loans assigned to this agent
            $query->whereIn('loan_overdue_stage_id', collect($myLoanStages)->pluck('id'));

        }


        $loans = $query->get();
        return response()->json(ApiResponse::successResponseWithData($loans));

    }

    public function getLoanSelectionInfo(): \Illuminate\Http\JsonResponse
    {
        // for now its always opened
        // later we'd add conditions to close it


        return response()->json(ApiResponse::successResponseWithData([
            'limit' => null,
            'status' => 'opened'
        ]));

    }


    public function getLoansByCategories(Request $request, string $category): \Illuminate\Http\JsonResponse
    {

        $user = $request->user();
        $query = LoanApplication::with(['latestStatus', 'assignedTo', 'user', 'stage', 'statuses'])
            ->where('closed','=', false)
            ->where('assigned_to', $user->id);

        $data = [];
        if($category == 'new-orders'){

            $data = $query->get();

        }else if ($category == 'unreachable') {

            // continue with query statement for the different categories

        }


        return response()->json(ApiResponse::successResponseWithData($data));

    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function addFollowUpRecord(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'remarks' => 'required',
            'loan_id' => 'required',
            'methods' => 'required|array'
        ]);

        $user = $request->user();
        $remarks = $request->get('remarks');
        $loanId = $request->get('loan_id');
        $methods = $request->get('methods');
        $recordTime = $request->get('record_time');

        $loan = LoanApplication::with([])->find($loanId);
        if(blank($loan)){
            throw new \Exception('Invalid request');
        }

        FollowUp::with([])->create([
            'loan_application_id' => $loanId,
            'agent_user_id' => $user->id,
            'method' => json_encode($methods),
            'remarks' => $remarks,
            'loan_stage_id' => $loan->{'loan_overdue_stage_id'},
            'record_time' => is_array($recordTime) ? json_encode($recordTime) : $recordTime,
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws ValidationException
     */
    public function addFollowUpSmsLog(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_application_id' => 'required',
            'message' => 'required',
        ]);

        $loanId = $request->get('loan_application_id');
        $message = $request->get('message');

        FollowUpSmsLog::with([])->create([
            'loan_application_id' => $loanId,
            'message' => $message,
            'agent_user_id' => $request->user()->id
        ]);

        $loan = LoanApplication::with([])->find($loanId);
        $customerUser = $loan->{'user'};
        // send sms to customer
        $customerUser->notify(new FollowUpSmsLogCreated(message: $message));

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws ValidationException
     */
    public function addFollowUpCallLog(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_application_id' => 'required',
        ]);

        $loanId = $request->get('loan_application_id');
        $agentUserId = $request->user()->{'id'};


        $lastFollowUpToday = FollowUpCallLog::with([])
            ->where('loan_application_id' ,'=', $loanId)
            ->where('agent_user_id' ,'=', $agentUserId)
            ->whereDate('created_at', '=', Carbon::today())
            ->first();

        if($lastFollowUpToday){
            $lastFollowUpToday->update([
                'count' => $lastFollowUpToday->{'count'} + 1
            ]);
        }else {
            FollowUpCallLog::with([])->create([
                'loan_application_id' => $loanId,
                'agent_user_id' => $agentUserId,
                'count' => 1
            ]);
        }

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws ValidationException
     */
    public function addFollowUpWhatsappLog(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'loan_application_id' => 'required',
        ]);

        $loanId = $request->get('loan_application_id');
        $agentUser = $request->user();

        $lastFollowUpToday = FollowUpWhatsappLog::with([])
            ->where([
                'loan_application_id' => $loanId,
                'agent_user_id' => $agentUser->{'id'}
            ])
            ->whereDate('created_at', '=', Carbon::today())->first();

        if($lastFollowUpToday){
            $lastFollowUpToday->update([
                'count' => $lastFollowUpToday->{'count'} + 1
            ]);
        }else {
            FollowUpWhatsappLog::with([])->create([
                'loan_application_id' => $loanId,
                'agent_user_id' => $agentUser->{'id'},
                'count' => 1
            ]);
        }

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    public function getCustomerKYC($userId): \Illuminate\Http\JsonResponse
    {

        $configsWithResponses = $this->getCusboardingPagesWithFieldsWithResponses($userId)->getData()->extra;
        return response()->json(ApiResponse::successResponseWithData($configsWithResponses));

    }






}

<?php

namespace App\Http\Controllers\Agent;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CommissionConfig;
use App\Models\Configuration;
use App\Models\FollowUp;
use App\Models\LoanApplication;
use App\Models\User;
use App\Notifications\AdminCreated;
use App\Notifications\NewAdminPasswordGenerated;
use App\Traits\AuthTrait;
use App\Traits\LoanApplicationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthTrait, LoanApplicationTrait;

    public function __construct()
    {
        $this->middleware('throttle:3,5')->only(['login']); // 3(maxAttempts).  // 5(decayMinutes)
    }

    public function loginAsAgent(Request $request): \Illuminate\Http\JsonResponse
    {
       $loginResults = $this->login($request);

       if(!$loginResults->getData()->status){
           return $loginResults;
       }

        // check if the user is an agent
        $extra = $loginResults->getData()->extra;
        $roles = $extra->roles;
        if(!in_array('agent', $roles)){
            return response()->json(ApiResponse::failedResponse('Sorry!, you do not have the role of an agent. Kindly contact support'));
        }

        return $loginResults;
    }

    public function getInitialData(Request $request): \Illuminate\Http\JsonResponse
    {

        /// Get configurations -------
        $user = $request->user();
        $agent = $user->agent;
        $loanStages = $this->getLoanStagesFromPermissions($request)->getData()->extra;

        $countAssignedTo = LoanApplication::with(['latestStatus', 'assignedTo', 'user'])
            ->where('closed','=', false)
            ->where('assigned_to', $user->id)->count();


        // use the db to count this after repayment feature is done
        $retrieved = 0;
        $rate = 0;

        if($countAssignedTo > 0) {
            $rate = ($retrieved / $countAssignedTo) * 100;
        }

        $generalConfig = Configuration::with([])->first();

        $lastFollowUp = FollowUp::with([])->where([
            'agent_user_id' => $user->id
        ])->orderByDesc('created_at')->first();

        return response()->json(ApiResponse::successResponseWithData([
            'agent' => $agent,
            'stages' => $loanStages,
            'tasks' => $countAssignedTo,
            'retrieved' => 0,
            'rate' => toNDecimalPlaces($rate) . "%",
            'agent_no' =>  "#".$agent->id,
            'commission' => toCurrencyFormat($agent->{'balance'}),
            'last_follow_up_at' => !blank($lastFollowUp) ? Carbon::parse($lastFollowUp->{'created_at'})->diffForHumans() : 'N/A',
            'app_link' => config('app.agent-url'),
            'developer_email' => config('custom.developer_email')
        ]));

    }
}

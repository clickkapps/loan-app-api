<?php

namespace App\Http\Controllers\Agent;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AgentTask;
use App\Models\Commission;
use App\Models\CommissionConfig;
use App\Models\Configuration;
use App\Models\FollowUp;
use App\Models\LoanApplication;
use App\Models\User;
use App\Notifications\AdminCreated;
use App\Notifications\NewAdminPasswordGenerated;
use App\Traits\AuthTrait;
use App\Traits\CommissionTrait;
use App\Traits\LoanApplicationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthTrait, LoanApplicationTrait, CommissionTrait;

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


        // use the db to count this after repayment feature is done

        $rate = 0;

        $taskForToday = AgentTask::with([])->where([
            'user_id' => $user->id,
            'date' => Carbon::today()
        ])->first();


        if($taskForToday->{'tasks_count'} > 0) {
            $rate = ($taskForToday->{'collected_count'} / $taskForToday->{'tasks_count'}) * 100;
        }

        $generalConfig = Configuration::with([])->first();

        $todayDesc = getTodayDescription();


        $lastFollowUp = FollowUp::with([])->where([
            'agent_user_id' => $user->id
        ])->orderByDesc('created_at')->first();

        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();

        $commission = Commission::with([])->where('user_id', $user->{'id'})
            ->where('created_at', '>=' , $startOfMonth)
            ->where('created_at', '<=', $endOfMonth)
            ->sum('amount');
//         = User::withCommissionSum($startOfMonth, $endOfMonth)->find($user->{'id'});

        return response()->json(ApiResponse::successResponseWithData([
            'agent' => $agent,
            'stages' => $loanStages,
            'tasks' => $taskForToday->{'tasks_count'},
            'retrieved' => $taskForToday->{'collected_count'},
            'general_config' => $generalConfig,
            'rate' => toNDecimalPlaces($rate) . "%",
            'agent_no' =>  "#".$agent->id,
            'commission' => toCurrencyFormat($commission),
            'last_follow_up_at' => !blank($lastFollowUp) ? Carbon::parse($lastFollowUp->{'created_at'})->diffForHumans() : 'N/A',
            'app_link' => config('app.agent-url'),
            'developer_email' => config('custom.developer_email'),
            'today_desc' => $todayDesc,
        ]));

    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\ConfigLoanOverdueStage;
use App\Models\User;
use App\Notifications\AdminCreated;
use App\Notifications\AgentAssigned;
use App\Notifications\AgentRevoked;
use App\Traits\RolePermissionTrait;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{

    use RolePermissionTrait;

    // add new administrator
    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {

        // check if user can create new admin
        $this->authorize('create', Admin::class);

        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);

        $name = $request->get('name');
        $email = $request->get('email');

        $userExists = User::where('email', $email)->exists();

        if($userExists) {
            throw new \Exception("Account with this email $email already exist");
        }

        $tempPassword = generateRandomNumber();

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make($tempPassword),
            'active' => true,
            'requires_password_reset' => true
        ]);

        $admin->refresh();
        $admin->assignRole(['admin']);

        // Notify user about account created and temporal password
        $admin->notify(new AdminCreated(tempPassword: $tempPassword));

        Log::info("new admin created: $email");

        return response()->json(ApiResponse::successResponseWithMessage('Account created'));

    }

    /**
     * @throws AuthorizationException
     */
    public function getAll() {

        $this->authorize('viewAny', Admin::class);
        return User::role('admin')->with(['permissions', 'roles'])->get();

    }

    /**
     * @throws AuthorizationException
     */
    public function getAdmin($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAdmin', Admin::class);

        $user = User::find($id);

        $lastLogin = $user->{'last_login'};
        return response()->json(ApiResponse::successResponseWithData([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'last_login' => $lastLogin,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->permissions
        ]));

    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws \Exception
     */
    public function makeAdminAnAgent(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('assignAgents', Admin::class);

        $this->validate($request, [
           'admin_id' => 'required',
           'permission' => 'required'
        ]);

        $adminId = $request->get('admin_id');
        $permission = $request->get('permission');

        if(!str_contains($permission, 'access to loan stage')){
            throw  new \Exception('Invalid permission. Agent should have access to one of the loan stages');
        }

        $user = User::find($adminId);
        if (blank($user)) {
            throw new  \Exception('Admin not found');
        }

        if($user->hasRole('agent')) {
            throw new  \Exception('Admin already has the role of an agent');
        }

        // Adding permissions via a role
        $user->assignRole('agent');
        $user->refresh();

        // create an agent account for user

        $agentAccountAlreadyCreated = Agent::with([])->where([
            'user_id' => $user->id,
        ])->exists();

        if(!$agentAccountAlreadyCreated) {
            $agent = Agent::with([])->create([
                    'user_id' => $user->id,
                ]
            );

            /// create a task for the agent for today ---------------
            $agentId = $agent->{'id'};
            $agentUserId = $agent->{'user_id'};
            $tasksCountRemaining = 0.0;
            $tasksAmountRemaining =  0.0;

            AgentTask::with([])->create([
                'user_id' => $agentUserId,
                'agent_id' => $agentId,
                'date' => Carbon::today(),
                'tasks_count' => $tasksCountRemaining,
                'collected_count' => 0,
                'tasks_amount' => $tasksAmountRemaining,
                'collected_amount' => 0,
            ]);

        }

        /// assign first permission to agent ---------
        $myRequest = new Request();
        $myRequest->setMethod('POST');
        $myRequest->request->add([
            'user_id' => $user->{'id'},
            'permission' => $permission,
            'status' => 'assign',
        ]);
        $this->assign($myRequest);


        $user->notify(new AgentAssigned());


        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws \Exception
     */
    public function unmakeAdminAnAgent(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('assignAgents', Admin::class);

        $this->validate($request, [
            'admin_id' => 'required'
        ]);

        $adminId = $request->get('admin_id');

        $user = User::find($adminId);
        if (blank($user)) {
            throw new  \Exception('Admin not found');
        }

        if(!$user->hasRole('agent')) {
            throw new  \Exception('Admin is not an agent');
        }

        $user->removeRole('agent');

        $user->notify(new AgentRevoked());

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     */
    public function getAgents(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAgents', Admin::class);

        $startOfMonth = !blank($request->get('start_date')) ? Carbon::parse($request->get('start_date')) : Carbon::today()->startOfMonth();
        $endOfMonth = !blank($request->get('end_date')) ? Carbon::parse($request->get('end_date')) : Carbon::today()->endOfMonth();

        $agents = User::role('agent')->with(['agent', 'roles','permissions'])->get();
////
//        ->whereHas('agent', function ($query) use ($startOfMonth, $endOfMonth)  {
//        $query
//            ->whereDate('created_at' , '>=' , $startOfMonth)
//            ->whereDate('created_at' , '<=',  $endOfMonth);
//    })

        if(!blank($request->get('stage'))) {
            $loanStage = $request->get('stage');
            $items = explode('-', $loanStage);
            $stageName = $items[1];
            $agents = $agents->filter(function($agent) use ($stageName){
                return $agent->hasPermissionTo("access to loan stage $stageName");
            })->values();
        }

        $agents->map(function ($agent) use ($startOfMonth, $endOfMonth) {
                $balance = User::withCommissionSum($startOfMonth, $endOfMonth)->find($agent->{'user_id'});
                $agent->balance = $balance;
        });

        // Adding permissions via a role

        // create an agent account for user
        return response()->json(ApiResponse::successResponseWithData($agents));

    }

    public function getAgentsCommissionDisplayStatus(): \Illuminate\Http\JsonResponse
    {
        $atLeastOneAgentShowBalanceIsFalse = Agent::with([])->where('show_balance', '=', false)->exists();
        return response()->json(ApiResponse::successResponseWithData(
           [
               'all_commissions_enabled' => !$atLeastOneAgentShowBalanceIsFalse
           ]
        ));
    }

    /**
     * @throws \Exception
     */
    public function getAgentInfo($userId): \Illuminate\Http\JsonResponse
    {

        $user = User::with([])->find($userId);
        if(blank($user)){
            throw new \Exception('User not found');
        }

        if(!$user->hasRole('agent')) {
            throw  new \Exception('User has no agent role');
        }

        $agentInfo = $user->{'agent'};
        return response()->json(ApiResponse::successResponseWithData($agentInfo));

    }


    /**
     * @throws \Exception
     */
    public function getLoansAssignedToAgent($userId): \Illuminate\Http\JsonResponse
    {

        $user = User::with([])->find($userId);
        if(blank($user)){
            throw new \Exception('User not found');
        }

        if(!$user->hasRole('agent')) {
            throw  new \Exception('User has no agent role');
        }

        $agentInfo = $user->{'agent'};
        return response()->json(ApiResponse::successResponseWithData($agentInfo));

    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function showCommissions(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('viewAgents', Admin::class);

        $this->validate($request, [
            'show_balance' => 'required|bool'
        ]);

        $query = Agent::with([]);
        if(!blank($request->get('user_id'))){
            $userId = $request->get('user_id');
            $query->where(['user_id' => $userId]);
        }
        $query->update([
            'show_balance' => $request->get('show_balance')
        ]);
        return response()->json(ApiResponse::successResponseWithMessage());

    }


}

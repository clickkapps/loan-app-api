<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\User;
use App\Notifications\AdminCreated;
use App\Notifications\AgentAssigned;
use App\Notifications\AgentRevoked;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
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
           'admin_id' => 'required'
        ]);

        $adminId = $request->get('admin_id');

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
            Agent::with([])->create([
                    'user_id' => $user->id,
                    'balance' => 0.00,
                ]
            );
        }

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
    public function getAgents(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAgents', Admin::class);

        $agents = User::role('agent')->with(['agent', 'roles','permissions'])->get();
        // Adding permissions via a role

        // create an agent account for user

        return response()->json(ApiResponse::successResponseWithData($agents));

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
}

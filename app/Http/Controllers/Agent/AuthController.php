<?php

namespace App\Http\Controllers\Agent;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminCreated;
use App\Notifications\NewAdminPasswordGenerated;
use App\Traits\AuthTrait;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthTrait;

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

        return response()->json(ApiResponse::successResponseWithData([
            'agent' => $agent
        ]));

    }
}

<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\NewAdminPasswordGenerated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait AuthTrait
{

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {

        try {

            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->get('email'))->first();

            if (! $user || ! Hash::check($request->get('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials'],
                ]);
            }

            if( blank($user->{'email_verified_at'})){
                throw ValidationException::withMessages([
                    'email' => ['This account has not been verified. Try signing up again and verify your account'],
                ]);
            }

            if( !$user->active ){
                throw ValidationException::withMessages([
                    'email' => ['This account is inactive. Please contact support'],
                ]);
            }

            $lastLogin = $user->{'last_login'};
            $user->update([
                'last_login' => now()
            ]);

            $verification = Verification::where('user_id', $user->id)->first();
            if(!blank($verification) && $verification->attempts > 0) {
                $verification->update([
                    'attempts' => 0
                ]);
            }


            $token = $user->createToken($user->email)->plainTextToken;

            return response()->json(ApiResponse::successResponseWithData([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_login' => $lastLogin,
                'token' => $token,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->permissions,
                'requires_password_reset' => $user->{'requires_password_reset'} == 1
            ]));

        }catch (\Exception $e) {
            return response()->json(ApiResponse::failedResponse($e->getMessage()));
        }
    }


    /**
     * @throws \Exception
     */
    public function setPassword(Request $request): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);


        $email = $request->get('email');
        $password = $request->get('password');
        $securityCode = $request->get('security_code');

        $user = User::where('email', $email)->first();

        if(blank($user)){
            throw new \Exception('Unauthorized action. Please contact administrators');
        }

        // If the user is a guest
        if(!$request->user()) {

            if(blank($securityCode)) {
                // unauthorized user trying to set password
                Log::info("unauthorized user trying to set password");
                throw new \Exception("Unauthorized action. Please contact administrators");
            }

            if (!Hash::check($securityCode, $user->password)) {
                throw new \Exception("Unauthorized action. Please contact administrators");
            }

        }

        $user->update([
            'password' => Hash::make($password),
            'requires_password_reset' => false
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());


    }

    /// Customer password reset request is already handled in the Customer/AuthController

    /**
     * @throws \Exception
     */
    public function requestAdminPasswordReset(Request $request): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'email' => 'required|string', // this is what we use to identify the user
        ]);

        $email = $request->get('email');
        $user = User::where('email', $email)->first();

        if(blank($user)){
            throw new \Exception('This account does not exist');
        }

        $securityCode = generateRandomNumber();

        $user->update([
            'password' => Hash::make($securityCode),
            'requires_password_reset' => true
        ]);

        // Notify user about account created and temporal password
        $user->notify(new NewAdminPasswordGenerated(tempPassword: $securityCode));

        return response()->json(ApiResponse::successResponseWithMessage('Verification'));

    }



}

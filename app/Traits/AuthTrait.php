<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if( blank($user->{'email_verified_at'})){
                throw ValidationException::withMessages([
                    'email' => ['This account has not been verified. Try signing up again and verify your email'],
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

            $token = $user->createToken($user->email)->plainTextToken;

            return response()->json(ApiResponse::successResponseV2([
                'name' => $user->name,
                'email' => $user->email,
                'last_login' => $lastLogin,
                'token' => $token
            ]));

        }catch (\Exception $e) {
            return response()->json(ApiResponse::failedResponse($e->getMessage()));
        }
    }

}

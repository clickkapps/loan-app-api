<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Verification;
use App\Traits\AuthTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->middleware('throttle:3,5')->only(['login']); // 3(maxAttempts).  // 5(decayMinutes)
    }

    /**
     * @throws \Exception
     */
    public function createAccount(Request $request) {

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string', // this is what we use to identify the user
            'password' => 'required|string',
        ]);

        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');

        // create an inactive user in the database
        $user = User::updateOrCreate(
            ['email' => $email ],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ],

        );

        // allow re-signup only if email has not been verified
        // probable the user started the signup process and neglected it on half-way
        // this will prevent ppl from activating their blocked account via signup
        if(!blank($user->{'email_verified_at'})) {
            throw new \Exception('This account already exist. Try login instead');
        }

        /// create and send verification code to the user's email
        $code = generateRandomNumber(6);

        Verification::updateOrCreate(
            ['user_id' => $user->id, 'verification_field'=> 'email'],
            ['code' => Hash::make($code)]
        );

        Log::info("verification code: $code");

        return response()->json(ApiResponse::successResponseWithMessage("A verification code has been sent to $email"));

    }

    public function verifyAccountOnSignup(Request $request): \Illuminate\Http\JsonResponse
    {

        try{

            $request->validate([
                'code' => 'required|string',
                'email' => 'required|string|email'
            ]);

            $email = $request->get('email');
            $code = $request->get('code');

            $user = User::where('email', $email)->first();

            if(blank($user)){
                throw new \Exception('This email does not exist');
            }

            // make sure the account is not already verified
            // cus some user would want to use this route to activate their account
            if(!blank($user->{'email_verified_at'})){
                throw new \Exception('This account has already been verified');
            }

            $verification = Verification::where('user_id', $user->id)->first();
            if(blank($verification)){
                throw new \Exception('Invalid account');
            }


            if(($verification->attempts + 1 ) > 5) {
                throw new \Exception('This account has been blocked due to so many unsuccessful attempts. Contact the support team');
            }

            $valid = Hash::check($code,$verification->code);

            $newCalculatedAttempts = $verification->attempts + 1;
            $verification->update([
                'attempts' => $newCalculatedAttempts
            ]);

            if(!$valid){
                // set the account as inactive if the attempts have reached 5 (meaning its suspicious)
                if($newCalculatedAttempts >= 5) {
                    $user->update([
                        'active' => false
                    ]);
                }
                throw new \Exception('Invalid verification code');
            }

            // mark email as verified
            $user->update([
                'email_verified_at' => now(),
                'active' => true
            ]);

            $verification->update([
                'status' => 'verified'
            ]);

            return response()->json(ApiResponse::successResponseWithMessage());

        }catch (\Exception $e) {

            return response()->json(ApiResponse::failedResponse($e->getMessage()));

        }

    }

}

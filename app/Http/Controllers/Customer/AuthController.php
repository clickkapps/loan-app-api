<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\AccountVerificationRequested;
use App\Providers\RouteServiceProvider;
use App\Traits\AuthTrait;
use App\Traits\CusboardingPageTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use AuthTrait, CusboardingPageTrait;

    public function __construct()
    {
//        $this->middleware('throttle:3,5')->only(['login']); // 3(maxAttempts).  // 5(decayMinutes)
    }

    /**
     * @throws \Exception
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string', // this is what we use to identify the user
            'password' => 'required|string'
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

        $user->refresh();
        $user->assignRole(['customer']);


        $previousCodeSent = Verification::with([])
            ->where(['user_id' => $user->id, 'verification_field' => 'email'])->first();

        $now = Carbon::now();
        $retryIntervalInMinutes = 6;

        // check if it has been at least 5 mins since code was sent
        // code should only be resent after 5 mins


        if($previousCodeSent && $previousCodeSent->{'code_generated_at'} && $now->lessThan(Carbon::parse($previousCodeSent->{'code_generated_at'})->addMinutes($retryIntervalInMinutes)) && Carbon::parse($previousCodeSent->{'code_generated_at'})->addMinutes($retryIntervalInMinutes)->diffInSeconds($now) != 0) {

            $retryInMinutes = Carbon::parse($previousCodeSent->{'code_generated_at'})->addMinutes($retryIntervalInMinutes)->diffInMinutes($now);
            $retryInSeconds = Carbon::parse($previousCodeSent->{'code_generated_at'})->addMinutes($retryIntervalInMinutes)->diffInSeconds($now);
            $response = "Verification code has already been sent to $email";
            Log::info("code already sent:  $retryInMinutes minute(s) remaining to retry");

        } else {

            /// create and send verification code to the user's email
            $code = generateRandomNumber();

            Verification::with([])->updateOrCreate(
                ['user_id' => $user->id, 'verification_field'=> 'email'],
                [
                    'code' => Hash::make($code),
                    'code_generated_at' => $now
                ]
            );

            $newCodeSent = Verification::with([])->where(['user_id' => $user->id, 'verification_field' => 'email'])->first();
            $retryInSeconds = Carbon::parse($newCodeSent->{'updated_at'})->addMinutes($retryIntervalInMinutes)->diffInSeconds($now);

            // send verification code
            $response = "A verification code has been sent to $email";

            $user->notify(new AccountVerificationRequested($code));

            Log::info("verification code sent: $code");

        }

        // create related customer table
        Customer::with([])->updateOrCreate(
            ['user_id' => $user->id ],
            []
        );


        return response()->json(ApiResponse::successResponseWithData(
            [
              'retry_in_seconds' => $retryInSeconds
            ],
            $response));

    }

    public function verifyAccountOnSignup(Request $request): \Illuminate\Http\JsonResponse
    {

        try{

            $request->validate([
                'code' => 'required|string',
                'email' => 'required|string',
                'activate_account' => 'required|bool'
            ]);

            $email = $request->get('email');
            $code = $request->get('code');
            $activateAccount = $request->get('activate_account');

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
                'attempts' => $newCalculatedAttempts,
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

            $dataToUpdate = [
                'email_verified_at' => now(),
            ];

            if($activateAccount) {
                $dataToUpdate += [
                    'active' => true
                ];
            }

            // mark email as verified
            $user->update($dataToUpdate);

            $verification->update([
                'status' => 'verified',
                'code_generated_at' => null
            ]);

            return response()->json(ApiResponse::successResponseWithMessage());

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
            'password' => Hash::make($password)
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());


    }

    /**
     * @throws \Exception
     */
    public function requestPasswordReset(Request $request): \Illuminate\Http\JsonResponse
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
           'email_verified_at' => null,
        ]);

        $myRequest = new Request();
        $myRequest->setMethod('POST');
        $myRequest->request->add([
            'name' => $user->name,
            'email' => $email,
            'password' => $securityCode,
        ]);

        $registrationResponse = $this->register($myRequest);

        $data = $registrationResponse->getData();

        if(!$data->status){
            throw new \Exception('Unauthorized action. Please contact administrators');
        }

        $extra = (array) $data->extra;

        $extra += [
            'security_code' => $securityCode,
            'name' => $user->name
        ];

        return response()->json(ApiResponse::successResponseWithData($extra, $data->message));

    }


    // All relevant data required to personalize the app
    public function getInitialData(Request $request): \Illuminate\Http\JsonResponse
    {

        /// Get configurations -------
        $user = $request->user();
        $customer = $user->customer;

        // general configurations
        $generalConfig = Configuration::with([])->first();

        $pagesWithFields = $this->getCusboardingPagesWithFieldsWithResponses()->getData()->extra;

        $data = [
            'loan_application_config' => [
                'loan_application_amount_limit' => $customer->{'loan_application_amount_limit'} ?: $generalConfig->{'loan_application_amount_limit'},
                'loan_application_duration_limit' => $customer->{'loan_application_duration_limit'} ?: $generalConfig->{'loan_application_duration_limit'},
                'loan_application_interest_percentage' => $customer->{'loan_application_interest_percentage'} ?: $generalConfig->{'loan_application_interest_percentage'},
            ],
            "agreement" => $customer->{'agreed_to_terms_or_service'},
            'cusboarding' => [
                'cusboarding_completed' =>  $customer->{'cusboarding_completed'},
                'pages_with_fields' => $pagesWithFields
            ]
        ];

        return response()->json(ApiResponse::successResponseWithData($data));

    }


}

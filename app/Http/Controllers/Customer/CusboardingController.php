<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigCusboardingField;
use App\Models\Configuration;
use App\Traits\CusboardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CusboardingController extends Controller
{
    use CusboardingPageTrait;


    // All relevant data required to personalize the app
    public function getInitialData(Request $request): \Illuminate\Http\JsonResponse
    {

        /// Get configurations -------
        $user = $request->user();
        $customer = $user->customer;

        // general configurations
        $generalConfig = Configuration::with([])->first();

        $pagesWithFields = $this->getCusboardingPagesWithFields()->getData()->extra;

        $data = [
          'loan_application_config' => [
              'loan_application_amount_limit' => $customer->{'loan_application_amount_limit'} ?: $generalConfig->{'loan_application_amount_limit'},
              'loan_application_duration_limit' => $customer->{'loan_application_duration_limit'} ?: $generalConfig->{'loan_application_duration_limit'},
              'loan_application_interest_percentage' => $customer->{'loan_application_interest_percentage'} ?: $generalConfig->{'loan_application_interest_percentage'},
          ],
          'cusboarding' => $pagesWithFields
        ];

        return response()->json(ApiResponse::successResponseWithData($data));

    }
}

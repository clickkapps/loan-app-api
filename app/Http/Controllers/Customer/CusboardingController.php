<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ConfigCusboardingField;
use App\Traits\CusboardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CusboardingController extends Controller
{
    use CusboardingPageTrait;

    public function getPagesWithFields(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->getCusboardingPagesWithFields();

    }

    // All relevant data required to personalize the app
    public function getInitialData(Request $request): \Illuminate\Http\JsonResponse
    {

        /// Get configurations -------


//         $this->getCusboardingPagesWithFields();

    }
}

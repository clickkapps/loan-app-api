<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\ConfigCusBoardingPage;

trait CusBoardingPageTrait
{
    public function getCusboardingPagesWithFields(): \Illuminate\Http\JsonResponse
    {
        $pageWithFields = ConfigCusBoardingPage::with('fields')->orderBy('page_position')->get();
        return response()->json(ApiResponse::successResponseWithData($pageWithFields));
    }
}

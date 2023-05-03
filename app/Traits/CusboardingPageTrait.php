<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\ConfigCusboardingPage;

trait CusboardingPageTrait
{
    public function getCusboardingPagesWithFields(): \Illuminate\Http\JsonResponse
    {
        $pageWithFields = ConfigCusboardingPage::with('fields')->orderBy('page_position')->get();
        return response()->json(ApiResponse::successResponseWithData($pageWithFields));
    }
}

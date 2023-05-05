<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\ConfigCusboardingPage;
use App\Models\Cusboarding;

trait CusboardingPageTrait
{
    public function getCusboardingPagesWithFields(): \Illuminate\Http\JsonResponse
    {
        $pageWithFields = ConfigCusboardingPage::with('fields')->orderBy('page_position')->get();
        return response()->json(ApiResponse::successResponseWithData($pageWithFields));
    }


    public function getCusboardingPagesWithFieldsWithResponses($userId): \Illuminate\Http\JsonResponse
    {
        $userResponses = Cusboarding::with([])->where('user_id', $userId)->get();

        // get configs
        $pagesWithFields =  $this->getCusboardingPagesWithFields();

        $configs = collect($pagesWithFields->getData()->extra);

        $configsWithResponses = $configs->map(function ($page) use($userResponses) {
            $fields = $page->{'fields'};
            $fieldsWithResponse = collect($fields)->map(function ($field) use ($userResponses) {
                $response = collect($userResponses)->firstWhere('field_name','=', $field->{'name'});
                $field->response = $response != null ? $response->{'response'} : null;
                return $field;
            });
            $page->{'fields'} = $fieldsWithResponse;
            return $page;
        });

        return response()->json(ApiResponse::successResponseWithData($configsWithResponses));
    }


}

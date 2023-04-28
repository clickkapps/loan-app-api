<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigCusBoardingField;
use App\Models\ConfigCusBoardingPage;
use App\Traits\CusBoardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfigCusBoardingController extends Controller
{
    use CusBoardingPageTrait;

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addPage(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configure customer on-boarding fields', ConfigCusBoardingPage::class);

        $pageTitle = $request->get('page_title');
        $pageDescription = $request->get('page_description');
        $pagePosition = $request->get('page_position');

        if(blank($pagePosition)){
            $orderedPagePosition = ConfigCusBoardingPage::orderByDesc('page_position')->get();
            $lastPage = $orderedPagePosition->first();
            if(!blank($lastPage)) {
                $pagePosition = $lastPage->{'page_position'}  + 1;
            }else{
                $pagePosition = 1;
            }
        }

        ConfigCusBoardingPage::updateOrCreate(
            ['page_position' => $pagePosition,],
            [
                'page_title' => $pageTitle,
                'page_description' => $pageDescription,
            ]
        );

        return response()->json(ApiResponse::successResponseWithMessage());

    }


    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addFieldToPage(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configure customer on-boarding fields', ConfigCusBoardingField::class);

        $this->validate($request, [
            'config_cusboarding_page_id' => 'required',
            'type' => 'required',
        ]);

        $pageId = $request->get('config_cusboarding_page_id');
        $type = $request->get('type');
        $name = $request->get('name');
        $placeholder = $request->get('placeholder');
        $required = $request->get('required');
        $fieldPosition = $request->get('$fieldPosition');
        $extra = $request->get('extra');

        if(blank($fieldPosition)){
            $orderedFieldPosition = ConfigCusBoardingField::orderByDesc('position')->get();
            $lastField = $orderedFieldPosition->first();
            if(!blank($lastField)) {
                $fieldPosition = $lastField->{'position'}  + 1;
            }else{
                $fieldPosition = 1;
            }
        }

        ConfigCusBoardingField::create([
            'config_cusboarding_page_id' => $pageId,
            'type' => $type,
            'required' => $required,
            'name' => $name,
            'placeholder' => $placeholder,
            'position' => $fieldPosition,
            'extra' => $extra
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removeField($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configure customer on-boarding fields', ConfigCusBoardingField::class);

        $field = ConfigCusBoardingField::find($id);

        if(blank($field)){
            throw new \Exception("Field not found");
        }
        $field->delete();

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     */
    public function getPagesWithFields(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configure customer on-boarding fields', ConfigCusBoardingField::class);
        return $this->getCusboardingPagesWithFields();

    }
}

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

        $this->authorize('configureCusboardingFields', ConfigCusBoardingPage::class);

        $pageTitle = $request->get('page_title');
        $pageDescription = $request->get('page_description');
        $pagePosition = $request->get('page_position');

        $positionExists = ConfigCusBoardingPage::where('page_position', $pagePosition)->exists();

        // if no page position is provided  or provided position does not exist in db-----------------
        // create as new page
        if(blank($pagePosition) || !$positionExists){

            $orderedPagePosition = ConfigCusBoardingPage::orderByDesc('page_position')->get();
            $lastPage = $orderedPagePosition->first();
            if(!blank($lastPage)) {
                $pagePosition = $lastPage->{'page_position'}  + 1;
            }else{
                $pagePosition = 1;
            }

            ConfigCusBoardingPage::create([
                    'page_title' => $pageTitle,
                    'page_description' => $pageDescription,
                    'page_position' => $pagePosition,
            ]);

        }else  {

            // If pagePosition is provided and it already exists in the db ------------

            // bring out all the pages from this position downwards and reorder them
            $orderedPages = ConfigCusBoardingPage::where('page_position', '>=', $pagePosition)->orderBy('page_position')->get();

            $data = [];
            $data[] = [
                'id' => null,
                'page_title' => $pageTitle,
                'page_description' => $pageDescription,
                'page_position' => $pagePosition,
            ];

            foreach ($orderedPages as $page) {
                $data[] = [
                    'id' => $page->id,
                    'page_title' => $page->{'page_title'},
                    'page_description' => $page->{'page_description'},
                    'page_position' => $page->{'page_position'} + 1,
                ];
            }

            ConfigCusBoardingPage::upsert($data, ['id'], ['page_position']);

        }

        return response()->json(ApiResponse::successResponseWithMessage());

    }


    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addFieldToPage(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configureCusboardingFields', ConfigCusBoardingField::class);

        $this->validate($request, [
            'config_cusboarding_page_id' => 'required',
            'type' => 'required',
        ]);

        $pageId = $request->get('config_cusboarding_page_id');
        $type = $request->get('type');
        $name = $request->get('name');
        $placeholder = $request->get('placeholder');
        $required = $request->get('required');
        $fieldPosition = $request->get('position');
        $extra = $request->get('extra');


        $positionExists = ConfigCusBoardingField::where([
            'config_cusboarding_page_id' => $pageId,
            'position' => $fieldPosition
        ])->exists();

        // if no field position is provided  or provided position does not exist in db-----------------
        if(blank($fieldPosition) || !$positionExists){
            $lastField = ConfigCusBoardingField::where('config_cusboarding_page_id', $pageId)->orderByDesc('position')->first();
            if(!blank($lastField)) {
                $fieldPosition = $lastField->{'position'}  + 1;
            }else{
                $fieldPosition = 1;
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

        } else {
            // If fieldPosition is provided and it already exists in the db ------------

            // bring out all the fields from this position downwards and reorder them
            $orderedFields = ConfigCusBoardingField::where('config_cusboarding_page_id', $pageId)
                ->where('position', '>=', $fieldPosition)->orderBy('position')->get();

            // recreate the fields with the appropriate positions

            $data = [];
            $data[] = [
                'id' => null,
                'config_cusboarding_page_id' => $pageId,
                'type' => $type,
                'required' => $required,
                'name' => $name,
                'placeholder' => $placeholder,
                'position' => $fieldPosition,
                'extra' => $extra
            ];


            foreach ($orderedFields as $field) {
                $data[] = [
                    'id' => $field->id,
                    'config_cusboarding_page_id' => $pageId,
                    'type' => $field->type,
                    'required' => $field->required,
                    'name' => $field->name,
                    'placeholder' => $field->placeholder,
                    'position' => $field->position + 1,
                    'extra' => $field->extra
                ];
            }

            ConfigCusBoardingField::upsert($data, ['id'], ['position']);


        }


        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removeField($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', ConfigCusBoardingField::class);

        // after removing a field, all positions must align accordingly

        $selectedField = ConfigCusBoardingField::find($id);
//
        if(blank($selectedField)){
            throw new \Exception("Field not found");
        }



        $orderedFields = ConfigCusBoardingField::where('config_cusboarding_page_id', $selectedField->{'config_cusboarding_page_id'})
            ->where('position', '>', $selectedField->position)->orderBy('position')->get();

        // recreate the fields with the appropriate positions

        $data = [];
        foreach ($orderedFields as $field) {
            $data[] = [
                'id' => $field->id,
                'config_cusboarding_page_id' => $field->{'config_cusboarding_page_id'},
                'type' => $field->type,
                'required' => $field->required,
                'name' => $field->name,
                'placeholder' => $field->placeholder,
                'position' => $field->position - 1,
                'extra' => $field->extra
            ];
        }

        $selectedField->delete();
        ConfigCusBoardingField::upsert($data, ['id'], ['position']);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removePage($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', ConfigCusBoardingPage::class);

        // after removing a field, all positions must align accordingly

        $selectedPage = ConfigCusBoardingPage::find($id);
//
        if(blank($selectedPage)){
            throw new \Exception("Field not found");
        }



        $orderedPages = ConfigCusBoardingPage::where('page_position', '>', $selectedPage->{'page_position'})->orderBy('page_position')->get();

        // recreate the fields with the appropriate positions

        $data = [];
        foreach ($orderedPages as $page) {
            $data[] = [
                'id' => $page->id,
                'page_title' => $page->{'page_title'},
                'page_description' => $page->{'page_description'},
                'page_position' => $page->{'page_position'} - 1,
            ];
        }

        $selectedPage->delete();
        ConfigCusBoardingPage::upsert($data, ['id'], ['page_position']);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     */
    public function getPagesWithFields(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', ConfigCusBoardingField::class);
        return $this->getCusboardingPagesWithFields();

    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigCusboardingField;
use App\Models\ConfigCusboardingPage;
use App\Models\Configuration;
use App\Models\Cusboarding;
use App\Models\Customer;
use App\Traits\CusboardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfigCusboardingController extends Controller
{
    use CusboardingPageTrait;

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addPage(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configureCusboardingFields', Configuration::class);

        $pageTitle = $request->get('page_title');
        $pageDescription = $request->get('page_description');
        $pagePosition = $request->get('page_position');

        $positionExists = ConfigCusboardingPage::where('page_position', $pagePosition)->exists();

        // if no page position is provided  or provided position does not exist in db-----------------
        // create as new page
        if(blank($pagePosition) || !$positionExists){

            $orderedPagePosition = ConfigCusboardingPage::orderByDesc('page_position')->get();
            $lastPage = $orderedPagePosition->first();
            if(!blank($lastPage)) {
                $pagePosition = $lastPage->{'page_position'}  + 1;
            }else{
                $pagePosition = 1;
            }

            ConfigCusboardingPage::create([
                    'page_title' => $pageTitle,
                    'page_description' => $pageDescription,
                    'page_position' => $pagePosition,
            ]);

        }else  {

            // If pagePosition is provided and it already exists in the db ------------

            // bring out all the pages from this position downwards and reorder them
            $orderedPages = ConfigCusboardingPage::where('page_position', '>=', $pagePosition)->orderBy('page_position')->get();

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

            ConfigCusboardingPage::upsert($data, ['id'], ['page_position']);

        }

        return response()->json(ApiResponse::successResponseWithMessage());

    }


    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addFieldToPage(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configureCusboardingFields', Configuration::class);

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
        $extra = !blank($extra) ? json_encode($extra) : null;


        $positionExists = ConfigCusboardingField::where([
            'config_cusboarding_page_id' => $pageId,
            'position' => $fieldPosition
        ])->exists();

        // if no field position is provided  or provided position does not exist in db-----------------
        if(blank($fieldPosition) || !$positionExists){
            $lastField = ConfigCusboardingField::where('config_cusboarding_page_id', $pageId)->orderByDesc('position')->first();
            if(!blank($lastField)) {
                $fieldPosition = $lastField->{'position'}  + 1;
            }else{
                $fieldPosition = 1;
            }

            ConfigCusboardingField::create([
                'config_cusboarding_page_id' => $pageId,
                'type' => $type,
                'required' => $required,
                'name' => $name,
                'placeholder' => $placeholder,
                'position' => $fieldPosition,
                'extra' => $extra
            ]);

            // when new field is added , customers will have to fill that field when applying for loan


        } else {
            // If fieldPosition is provided and it already exists in the db ------------

            // bring out all the fields from this position downwards and reorder them
            $orderedFields = ConfigCusboardingField::where('config_cusboarding_page_id', $pageId)
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

            ConfigCusboardingField::with([])->upsert($data, ['id'], ['position']);

        }

        Customer::with([])->where('cusboarding_completed', '=',true)->update([
            'cusboarding_completed' => false
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws \Exception
     */
    public function updateFieldValues(Request $request, $fieldId): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);

        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);

        $type = $request->get('type');
        $name = $request->get('name');
        $placeholder = $request->get('placeholder');
        $required = $request->get('required');
        $extra = $request->get('extra');
        $extra = !blank($extra) ? json_encode($extra) : null;

        $field = ConfigCusboardingField::with([])->find($fieldId);

        if(blank($field)){
            throw new \Exception("Invalid field id: $fieldId");
        }

        // if the field name updates then update cusboarding responses field name
        if($field->{'name'} != $name) {
            Cusboarding::with([])->where('field_name',$field->{'name'})->update([
                'field_name' => $name
            ]);
        }

        $field->update([
            'type' => $type,
            'required' => $required ?: $field->{'required'},
            'name' => $name,
            'placeholder' => $placeholder,
            'extra' => $extra
        ]);


        return  response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removeField($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);

        // after removing a field, all positions must align accordingly

        $selectedField = ConfigCusboardingField::find($id);
//
        if(blank($selectedField)){
            throw new \Exception("Field not found");
        }



        $orderedFields = ConfigCusboardingField::where('config_cusboarding_page_id', $selectedField->{'config_cusboarding_page_id'})
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
        ConfigCusboardingField::upsert($data, ['id'], ['position']);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removePage($id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);

        // after removing a field, all positions must align accordingly

        $selectedPage = ConfigCusboardingPage::find($id);
//
        if(blank($selectedPage)){
            throw new \Exception("Field not found");
        }



        $orderedPages = ConfigCusboardingPage::where('page_position', '>', $selectedPage->{'page_position'})->orderBy('page_position')->get();

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
        ConfigCusboardingPage::upsert($data, ['id'], ['page_position']);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     */
    public function getPagesWithFields(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);
        return $this->getCusboardingPagesWithFields();

    }


    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws \Exception
     */
    public function reAssignFieldToPage(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);

        $this->validate($request, [
            'config_cusboarding_page_id' => 'required',
            'field_id' => 'required',
        ]);

        $pageId = $request->get('config_cusboarding_page_id');
        $fieldId = $request->get('field_id');

        $field = ConfigCusboardingField::with([])->find($fieldId);

        if($field->{'config_cusboarding_page_id'} == $pageId) {
            throw new \Exception("Field already assigned to page $pageId");
        }

        $type = $field->{'type'};
        $name = $field->{'name'};
        $placeholder = $field->{'placeholder'};
        $required = $field->{'required'};
        $extra = $field->{'extra'};
        $extra = !blank($extra) ? json_decode($extra) : null;

        $myRequest = new \Illuminate\Http\Request();
        $myRequest->setMethod('POST');
        $myRequest->request->add([
            'config_cusboarding_page_id' => $pageId,
            'type' => $type,
            'name' => $name,
            'placeholder' => $placeholder,
            'required' => $required,
            'extra' => $extra
        ]);

        $this->removeField($fieldId);
        $this->addFieldToPage($myRequest);


        return  response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws \Exception
     */
    public function reAssignFieldToPosition(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureCusboardingFields', Configuration::class);

        $this->validate($request, [
            'position' => 'required',
            'field_id' => 'required',
        ]);

        $fieldPosition= $request->get('position');
        $fieldId = $request->get('field_id');

        $field = ConfigCusboardingField::with([])->find($fieldId);


        $pageId = $field->{'config_cusboarding_page_id'};
        $type = $field->{'type'};
        $name = $field->{'name'};
        $placeholder = $field->{'placeholder'};
        $required = $field->{'required'};
        $extra = $field->{'extra'};
        $extra = !blank($extra) ? json_decode($extra) : null;

        $myRequest = new \Illuminate\Http\Request();
        $myRequest->setMethod('POST');
        $myRequest->request->add([
            'config_cusboarding_page_id' => $pageId,
            'type' => $type,
            'name' => $name,
            'placeholder' => $placeholder,
            'required' => $required,
            'extra' => $extra,
            'position' => $fieldPosition
        ]);

        $this->removeField($fieldId);
        $this->addFieldToPage($myRequest);


        return  response()->json(ApiResponse::successResponseWithMessage());

    }



    /**
     * @throws AuthorizationException
     */
    public function getConfigurations(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        // get configurations

        $config = Configuration::with([])->first();
        return response()->json(ApiResponse::successResponseWithData($config));

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function updateConfigurations(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $config = Configuration::with([])->find($id);
        if(blank($config)) {
            throw new \Exception("Invalid config id: $id");
        }
        // get configurations
        $config->update($request->all());
        return response()->json(ApiResponse::successResponseWithMessage());

    }
}

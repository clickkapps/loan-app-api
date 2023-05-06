<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigCusboardingField;
use App\Models\Cusboarding;
use App\Models\Customer;
use App\Traits\CusboardingPageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CusboardingController extends Controller
{
    use CusboardingPageTrait;

    /**
     * @throws ValidationException
     */
    public function setKYCResponses(Request $request) {

        $user = $request->user();

        $this->validate($request, [
           'data' => 'required|array'
        ]);

        $data = $request->get('data');
        $encoded = json_encode($data);
        Log::info("data: $encoded");


        $dataToUpsert = [];
        foreach ($data as $d) {
            $dataToUpsert[] = [
                'field_name' => $d['field_name'],
                'field_type' => $d['field_type'],
                'response' =>  $d['field_value'],
                'user_id' => $user->id
            ];
        }

        Cusboarding::with([])->upsert($dataToUpsert, ['user_id','field_name'], ['response', 'field_type']);

        $this->evaluateCustomerKYCStatus($user->id);

        $cusboardingStatus = $this->fetchCustomerKYCStatus($user->id)->getData()->extra;

        return response()->json(ApiResponse::successResponseWithData([
            'cusboarding_completed' => $cusboardingStatus
        ]));

    }

    public function evaluateCustomerKYCStatus($userId) {

        $requiredFieldNames = ConfigCusboardingField::with([])->where('required', true)->pluck('name')->toArray();
        $submittedFieldNames = Cusboarding::with([])
            ->where('user_id',  $userId,)
            ->where( function ($query) {
                $query->where('response' , '!=', '')
                    ->orWhere('response', '!=', null);
            })
            ->pluck('field_name')->toArray();

        $containsAllValues = !array_diff($requiredFieldNames, $submittedFieldNames);

        $updateFields =  [
            'cusboarding_completed' => false
        ];
        if($containsAllValues) {
            $updateFields = [
                'cusboarding_completed' => true
            ];
        }

        Customer::with([])->where('user_id', $userId)->update($updateFields);

    }

    public function fetchCustomerKYCStatus($userId): \Illuminate\Http\JsonResponse
    {

       $kycStatus = Customer::with([])->where('user_id', $userId)->first()->{'cusboarding_completed'};
       return response()->json(ApiResponse::successResponseWithData($kycStatus));

    }

}

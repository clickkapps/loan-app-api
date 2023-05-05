<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cusboarding;
use App\Models\Customer;
use App\Models\User;
use App\Traits\CusboardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;

class CustomerController extends Controller
{
    use CusboardingPageTrait;

    public function getCustomers(): \Illuminate\Http\JsonResponse
    {
        $customers = User::role('customer')->with('customer')->paginate(20);
        return response()->json(ApiResponse::successResponseWithData($customers));
    }

    /**
     * @throws \Exception
     */
    public function getCustomer($userId): \Illuminate\Http\JsonResponse
    {
        $customer = User::with('customer')->find($userId);
        if(!$customer->hasRole('customer')) {
            throw new \Exception("No customer with id: $userId");
        }
        return response()->json(ApiResponse::successResponseWithData($customer));
    }

    /**
     *
     * @throws AuthorizationException
     */
    public function getCustomerKYC($userId): \Illuminate\Http\JsonResponse
    {

        $this->authorize('view customer kyc', Customer::class);

        $configsWithResponses = $this->getCusboardingPagesWithFieldsWithResponses($userId)->getData()->extra;

        $fieldsCollection = collect();
        foreach (collect($configsWithResponses) as $pages) {
            $fieldsCollection = $fieldsCollection->toBase()->merge(collect($pages->fields));
        }

        // update all the configs with

        return response()->json(ApiResponse::successResponseWithData($fieldsCollection));

    }
}

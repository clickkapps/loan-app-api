<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cusboarding;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;

class CustomerController extends Controller
{
    public function getCustomers(): \Illuminate\Http\JsonResponse
    {
        $customers = User::role('customer')->with('customer')->paginate(20);
        return response()->json(ApiResponse::successResponseWithData($customers));
    }

    public function getCustomer($userId): \Illuminate\Http\JsonResponse
    {
        $customer = User::with('customer')->find($userId);
        return response()->json(ApiResponse::successResponseWithData($customer));
    }

    /**
     *
     * @throws AuthorizationException
     */
    public function getCustomerKYC($userId): \Illuminate\Http\JsonResponse
    {

        $this->authorize('view customer kyc', Customer::class);


        $kyc = Cusboarding::with([])->where('user_id', $userId)->get();
        return response()->json(ApiResponse::successResponseWithData($kyc));

    }
}

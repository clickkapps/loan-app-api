<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;

class CustomerController extends Controller
{
    public function getCustomers(): \Illuminate\Http\JsonResponse
    {
        $customers = User::role('customer')->get();
        return response()->json(ApiResponse::successResponseWithData($customers));
    }

    /**
     *
     * @throws AuthorizationException
     */
    public function getCustomerKYCByCustomerId($userId) {

        $this->authorize('view customer kyc', Customer::class);



    }
}

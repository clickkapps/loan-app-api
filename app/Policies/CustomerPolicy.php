<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewKYC(User $user) : bool {

        return $user->hasPermissionTo('view customer kyc');

    }
}

<?php

namespace App\Policies;

use App\Models\User;

class SupportPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function manageSupport(User $user) : bool {

        return  $user->hasPermissionTo('manage customer support');

    }
}

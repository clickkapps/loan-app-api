<?php

namespace App\Policies;

use App\Models\User;

class ConfigCusBoardingPagePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function configureCusboardingFields(User $user) : bool {
        return  $user->hasPermissionTo('configure customer on-boarding fields');
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class ConfigurationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function configureLoanApplicationParameters(User $user) : bool {

        return $user->hasPermissionTo('configuration loan application parameters');

    }

    public function configureCusboardingFields(User $user) : bool {

        return  $user->hasPermissionTo('configure customer on-boarding fields');

    }

}

<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function assignPermissions(User $user) : bool {
        return $user->hasPermissionTo('assign permissions');
    }

    public function viewPermissions(User $user): bool {
        return $user->hasPermissionTo('view permissions');
    }
 }

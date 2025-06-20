<?php

namespace App\Policies;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view all admins');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create admin');
    }

    public function viewAdmin(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin');
    }

    public function assignAgents(User $user): bool
    {
        return  $user->hasPermissionTo('assign agents');
    }


    public function viewAgents(User $user): bool
    {
        return  $user->hasPermissionTo('view agents');
    }


}

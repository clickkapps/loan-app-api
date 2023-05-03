<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\ConfigCusboardingField;
use App\Models\Configuration;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\ConfigurationPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\RolePermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

        Permission::class => RolePermissionPolicy::class,
        Configuration::class => ConfigurationPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super admin')) {
                return true;
            }
        });
    }
}

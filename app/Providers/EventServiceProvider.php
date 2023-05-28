<?php

namespace App\Providers;

use App\Events\PaymentCallbackReceived;
use App\Events\PaymentStatusReceived;
use App\Events\PermissionAssigned;
use App\Listeners\EvaluateMajorPermissions;
use App\Listeners\ProcessLoanApproval;
use App\Listeners\ProcessLoanRepayment;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PermissionAssigned::class => [
            EvaluateMajorPermissions::class
        ],
        PaymentCallbackReceived::class => [
            ProcessLoanApproval::class,
            ProcessLoanRepayment::class,
            // process deferment
            // process loan repayment
            // etc
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

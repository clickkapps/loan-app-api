<?php

namespace App\Listeners;

use App\Events\PaymentCallbackReceived;
use App\Events\PaymentStatusReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLoanApproval
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCallbackReceived $event): void
    {
        // update the loan status, that process is successful

    }
}

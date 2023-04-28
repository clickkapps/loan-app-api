<?php

namespace App\Listeners;

use App\Events\CusBoardingPageAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderlyArrangeCusBoardingPages
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
    public function handle(CusBoardingPageAdded $event): void
    {
        //
    }
}

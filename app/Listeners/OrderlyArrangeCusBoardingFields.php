<?php

namespace App\Listeners;

use App\Events\CusBoardingFieldAdded;
use App\Models\ConfigCusBoardingField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OrderlyArrangeCusBoardingFields
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
    public function handle(CusBoardingFieldAdded $event): void
    {
        $allCusboardingFields = ConfigCusBoardingField::all();
        $encoded = json_encode($allCusboardingFields);
        Log::info("old list: $encoded");

        $models = $allCusboardingFields->sortBy(function ($model) {
            return [$model->position, $model->created_at * -1];
        });

        $tasks = $allCusboardingFields->sortBy(function ($fields) {
            return $fields->position;
        });
        $encoded = json_encode($tasks);
        Log::info("new list: $encoded");
    }
}

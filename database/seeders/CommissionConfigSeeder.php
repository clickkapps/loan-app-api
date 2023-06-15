<?php

namespace Database\Seeders;

use App\Models\CommissionConfig;
use App\Models\ConfigCusboardingPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add account details page configuration
        $configExists = CommissionConfig::with([])->exists();
        if(!$configExists){
            CommissionConfig::with([])->create(
                [
                    'percentage_on_repayment_weekdays' => 0.0,
                    'percentage_on_repayment_weekends' => 0.0,
                    'percentage_on_deferment_weekdays' => 0.0,
                    'percentage_on_deferment_weekends' => 0.0,
                    'percentage_on_deferment_holidays' => 0.0
                ]

            );
        }


    }
}

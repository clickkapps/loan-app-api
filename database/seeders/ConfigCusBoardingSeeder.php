<?php

namespace Database\Seeders;

use App\Models\ConfigCusboardingPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigCusBoardingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add account details page configuration
        ConfigCusboardingPage::updateOrCreate(
            ['page_position' => 1,],
            [
                'page_title' => 'Basic info',
                'page_description' => 'Provide us with your basic information',
            ]
        );

        // Account number
    }
}

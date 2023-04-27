<?php

namespace Database\Seeders;

use App\Models\ConfigCusBoardingPage;
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
        ConfigCusBoardingPage::updateOrCreate([
            'key' => 'account_details_page',
            ''
        ]);

        // Account number
    }
}

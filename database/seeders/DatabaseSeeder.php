<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(ConfigCusBoardingSeeder::class);
        $this->call(ConfigurationTableSeeder::class);
        $this->call(CustomerTableSeeder::class);
        $this->call(ConfigLoanOverdueStageSeeder::class);
    }
}

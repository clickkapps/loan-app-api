<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /// High level roles and permissions --------
        Permission::create(['name' => 'admin management module']);
        Permission::create(['name' => 'customer kyc module']);
        Permission::create(['name' => 'recovery officers module']);
        Permission::create(['name' => 'roles and permissions module']);
        Permission::create(['name' => 'account settings module']);
        Permission::create(['name' => 'customer support module']);

        /// Sub level roles and permissions will be here --------

    }
}

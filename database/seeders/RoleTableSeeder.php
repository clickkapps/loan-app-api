<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'super admin'], []);
        Role::updateOrCreate(['name' => 'admin'], []);
        Role::updateOrCreate(['name' => 'agent'], []);
        Role::updateOrCreate(['name' => 'customer'], []);
    }
}

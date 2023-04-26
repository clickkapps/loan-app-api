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
        /// roles and permissions --------
        $adminPermissions = config('custom.admin_permissions');
        foreach ($adminPermissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['major']], []);
            foreach ($permission['subs'] as $sub) {
                Permission::updateOrCreate(['name' => $sub], []);
            }
        }

    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'clickkapps@gmail.com' ],
            [
            'name' => 'Super Admin',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'active' => true,
            ]
        );

        $admin->refresh();
        $admin->assignRole(['super admin']);

    }
}

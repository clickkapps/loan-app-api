<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerUserIds = User::role('customer')->pluck('id');
        $data = [];
        foreach ($customerUserIds as $id) {

            $data[] = [
                'user_id' => $id,
            ];

        }

        Customer::upsert($data,['user_id'],[]);

    }
}

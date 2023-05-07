<?php

namespace Database\Seeders;

use App\Models\ConfigLoanOverdueStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigLoanOverdueStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add account details page configuration
        $exists = ConfigLoanOverdueStage::with([])->where('id', 1)->exists();
        if(!$exists) {
            ConfigLoanOverdueStage::with([])->create(
                [
                    'name' => 'T0',
                    'desc' => 'This configuration applies when loan application is not overdue. This config is system generated',
                    'days_after_deadline' => 0,
                    'interest_percentage_per_day' => 0,
                    'installment_enabled' => false,
                    'auto_deduction_enabled' => false,
                    'percentage_raise_on_next_loan_request' => 50,
                    'eligible_for_next_loan_request' => true,
                    'key' => 'not_overdue'
                ]
            );
        }

    }
}

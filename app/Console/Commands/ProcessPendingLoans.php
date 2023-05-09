<?php

namespace App\Console\Commands;

use App\Models\Configuration;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Traits\LoanApplicationTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class ProcessPendingLoans extends Command implements Isolatable
{
    use LoanApplicationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-pending-loans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command works on all pending loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // check if approve loan config is auto
        $config = Configuration::with([])->first();
        if(blank($config)){
            return;
        }

        $autoApproval = $config->{'auto_loan_approval'};
        if(!$autoApproval){
            return;
        }

        // Pick n pending loans (say 10)
        $pendingLoanIds = LoanApplicationStatus::with([])->where('status', 'requested')->pluck('loan_application_id');
        $loans = LoanApplication::with(['latestStatus'])
            ->whereIn('id', $pendingLoanIds)
            ->where('locked', '=',false)
            ->orderByDesc('created_at')->get();

        // for each one of them, check if the required fields are fully filled
        foreach ($loans as $loan) {
            $kycStatus = Customer::with([])->where('user_id', '',$loan->{'user_id'})->first()->{'cusboarding_completed'};
            if(!$kycStatus) {
                // record application status
                LoanApplicationStatus::with([])->create([
                    'loan_application_id' => $application->{'id'},
                    'status' => 'requested',
                    'user_id' => $user->id,
                    'created_by' => 'customer'
                ]);
                return;
            }
        }

        // for each of them initiate loan disbursal

        $this->initiateLoanDisbursal(loan: null);

    }
}

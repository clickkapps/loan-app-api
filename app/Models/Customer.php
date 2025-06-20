<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'cusboarding_completed',
        'loan_application_amount_limit',
        'loan_application_duration_limit',
        'loan_application_interest_percentage',
        'agreed_to_terms_or_service',
        'default_momo_account_number',
        'default_momo_account_name',
        'default_momo_network',
        'eligibility_for_next_loan'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_amount_limit',
        'loan_application_duration_limit',
        'loan_application_interest_percentage',
        'deferment_percentage',
        'processing_fee_percentage',
        'auto_loan_approval',
        'pause_all_interests',
        'today_is_holiday',
        'show_customer_call_logs',
        'allow_agent_pick_loan_orders'
    ];
}

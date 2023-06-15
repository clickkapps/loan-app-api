<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigLoanOverdueStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'from_days_after_deadline',
        'to_days_after_deadline',
        'interest_percentage_per_day',
        'installment_enabled',
        'auto_deduction_enabled',
        'percentage_raise_on_next_loan_request',
        'eligible_for_next_loan_request',
        'key',
        'jargon'
    ];

    public function commission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CommissionConfig::class, 'stage_name', 'name');
    }
}

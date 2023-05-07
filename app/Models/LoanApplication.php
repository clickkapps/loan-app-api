<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'account_name',
        'network_type',
        'deadline',
        'amount_requested',
        'amount_disbursed',
        'fee_charged',
        'amount_to_pay',
        'loan_overdue_stage_id',
        'completed'
    ];

    public function statuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanApplicationStatus::class)->orderByDesc('created_at');
    }

    public function latestStatus(): Model|\Illuminate\Database\Eloquent\Relations\HasOne|null
    {
        return $this->hasOne(LoanApplicationStatus::class)->orderByDesc('created_at');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConfigLoanOverdueStage::class, 'loan_overdue_stage_id');
    }
}

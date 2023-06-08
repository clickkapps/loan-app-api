<?php

namespace App\Models;

use Carbon\Carbon;
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
        'accumulated_interest',
        'amount_to_pay',
        'loan_overdue_stage_id',
        'completed',
        'locked',
        'assigned_to'
    ];

    public function statuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanApplicationStatus::class)->orderBy('created_at');
    }

    public function latestStatus(): Model|\Illuminate\Database\Eloquent\Relations\HasOne|null
    {
        return $this->hasOne(LoanApplicationStatus::class)->orderByDesc('created_at');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConfigLoanOverdueStage::class, 'loan_overdue_stage_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to','id');
    }

    public function assignedToUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, LoanAssignedTo::class);
    }

    public function scopeLatestStatusName($query, $name)
    {
        return $query->whereHas('statuses', function ($query) use ($name) {
            $query->where('status', $name)
                ->where('created_at', function ($subQuery) {
                    $subQuery->selectRaw('MAX(created_at)')
                        ->from('loan_application_statuses')
                        ->whereColumn('loan_application_id', 'loan_applications.id');
                });
        });

    }


}

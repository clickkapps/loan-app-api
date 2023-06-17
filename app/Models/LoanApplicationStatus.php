<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplicationStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_id',
        'status',
        'user_id',
        'created_by',
        'agent_user_id',
        'extra'
    ];

    public function loanApplication(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

}

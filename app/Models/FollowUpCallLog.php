<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpCallLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_id',
        'agent_user_id',
        'count'
    ];

    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }
}

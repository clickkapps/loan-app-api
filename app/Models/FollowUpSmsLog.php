<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpSmsLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_id',
        'message',
        'agent_user_id'
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

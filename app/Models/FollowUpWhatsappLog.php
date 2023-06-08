<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpWhatsappLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_id',
        'agent_user_id',
        'count'
    ];
}

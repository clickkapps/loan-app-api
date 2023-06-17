<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'agent_id',
        'tasks_count',
        'collected_count',
        'tasks_amount',
        'collected_amount',
        'date'
    ];
}

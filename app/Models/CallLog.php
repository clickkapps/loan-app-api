<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timestamp',
        'name',
        'phone',
        'duration',
        'call_type'
    ];

}

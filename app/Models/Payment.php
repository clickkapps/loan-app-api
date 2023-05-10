<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_application_id',
        'client_ref',
        'server_ref',
        'amount',
        'account_number',
        'account_name',
        'network_type',
        'title',
        'description',
        'response_message',
        'response_code',
        'status',
        'extra',

    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_application_amount_limit',
        'loan_application_duration_limit',
        'loan_application_interest_percentage'
    ];
}

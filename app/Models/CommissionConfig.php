<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionConfig extends Model
{
    use HasFactory;
    protected $fillable = [
        'percentage_on_repayment_weekdays',
        'percentage_on_repayment_weekends',
        'percentage_on_deferment_weekdays',
        'percentage_on_deferment_weekends',
        'percentage_on_deferment_holidays',
        'percentage_on_repayment_holidays',
        'stage_name'
    ];
}

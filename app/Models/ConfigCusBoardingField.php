<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCusBoardingField extends Model
{
    use HasFactory;
    protected $table = 'config_cusboarding_fields';

    protected $fillable = [
        'config_cusboarding_page_id',
        'type',
        'required',
        'name',
        'placeholder',
        'key',
        'position',
        'extra'
    ];

}

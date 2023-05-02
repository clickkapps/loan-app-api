<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'code', 'attempts', 'status', 'verification_field',
        'code_generated_at'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCusBoardingPage extends Model
{
    use HasFactory;
    protected $table = 'config_cusboarding_pages';

    protected $fillable = [
        'page_title',
        'page_description',
        'page_position',
        'key'
    ];

    public function fields(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConfigCusBoardingField::class, 'config_cusboarding_page_id');
    }
}

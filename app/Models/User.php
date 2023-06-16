<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'last_login',
        'active',
        'requires_password_reset'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function kyc(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cusboarding::class);
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function assignedToLoans(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(LoanApplication::class, LoanAssignedTo::class);
    }

    public function commissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function scopeWithCommissionSum($query, $start, $end)
    {
        $query->with(['commissions' => function ($query) use ($start, $end) {
            $query->select('user_id', DB::raw('SUM(amount) as total_amount'))
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('user_id');
        }]);
    }

}

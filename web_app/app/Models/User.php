<?php

namespace App\Models;

use App\Models\Bet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'referral_code',
        'referred_by',
        'last_login'
    ];

    /*
    |--------------------------------------------------------------------------
    | HIDDEN FIELDS
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | USER STATS (relacja 1:1)
    |--------------------------------------------------------------------------
    */

    public function stats(): HasOne
    {
        return $this->hasOne(
            UserStat::class,
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REFERRAL SYSTEM (użytkownik który polecił innego uzytkownika)
    |--------------------------------------------------------------------------
    */

    public function referrer()
    {
        return $this->belongsTo(
            User::class,
            'referred_by'
        );
    }

    public function referrals()
    {
        return $this->hasMany(
            User::class,
            'referred_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USER -> BETS (RELACJA 1:N)
    |--------------------------------------------------------------------------
    */

public function bets()
{
    return $this->hasMany(
        Bet::class,
        'user_id'
    );
}

public function settledBets()
{
    return $this->hasMany(
        Bet::class,
        'user_id'
    )
        ->whereIn('status', [
            'won',
            'lost'
        ]);
}

public function recentFormBets()
{
    return $this->hasMany(
        Bet::class,
        'user_id'
    )
        ->whereIn('status', [
            'won',
            'lost'
        ])
        ->latest('settled_at');
}
}

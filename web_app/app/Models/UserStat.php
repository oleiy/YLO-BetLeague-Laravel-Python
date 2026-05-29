<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStat extends Model
{
    protected $table = 'user_stats';

    protected $fillable = [
        'user_id',
        'balance_points',
        'total_bets',
        'won_bets',
        'lost_bets',
        'accuracy_rate',
        'yield',
        'current_streak',
        'best_streak',
        'referral_count',

        /*
        |--------------------------------------------------------------------------
        | BAN SYSTEM
        |--------------------------------------------------------------------------
        */

        'is_banned',
        'ban_until',

        /*
        |--------------------------------------------------------------------------
        | DAILY REWARDS
        |--------------------------------------------------------------------------
        */

        'daily_login_streak',
        'last_daily_reward',
        'total_referral_earned',
    ];

    protected $casts = [

        'last_daily_reward' => 'date',

        /*
        |--------------------------------------------------------------------------
        | BAN DATE
        |--------------------------------------------------------------------------
        */

        'ban_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}

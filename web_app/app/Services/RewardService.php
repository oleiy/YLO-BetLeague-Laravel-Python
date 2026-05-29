<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;


class RewardService
{
    /**
     * BONUS ZA CODZIENNE LOGOWANIE
     */
    public function grantDailyLoginReward(
        User $user
    ): void {

        if (!$user->stats) {
            return;
        }

        $user->stats->increment(
            'balance_points',
            50
        );

        $user->last_login = Carbon::now();

        $user->save();
    }

    /**
     * BONUS ZA REFERRALA
     */
    public function grantReferralReward(
        User $user
    ): void {

        if (!$user->stats) {
            return;
        }

        $user->stats->increment(
            'balance_points',
            500
        );

        $user->stats->increment(
            'referral_count',
            1
        );
    }
}

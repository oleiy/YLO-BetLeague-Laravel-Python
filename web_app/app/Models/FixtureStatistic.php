<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixtureStatistic extends Model
{
    protected $table = 'fixture_statistics';

    protected $fillable = [
        'fixture_id',
        'home_corners',
        'away_corners',
        'home_yellow_cards',
        'away_yellow_cards',
        'home_red_cards',
        'away_red_cards',
        'home_shots_on_goal',
        'away_shots_on_goal'
    ];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }
}

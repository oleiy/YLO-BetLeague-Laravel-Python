<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    protected $casts = [
        'match_date' => 'datetime',
    ];

    protected $appends = [
        'match_date_local'
    ];

    /**
     * Lokalna data dla frontendu (Polska timezone)
     */
    public function getMatchDateLocalAttribute()
    {
        return $this->match_date
            ->timezone('Europe/Warsaw')
            ->format('Y-m-d H:i:s');
    }

    // Relacja: Mecz ma gospodarza
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    // Relacja: Mecz ma gościa
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    // Relacja: Mecz należy do danej ligi
    public function league()
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    // Relacja: Do meczu przypisane są kursy bukmacherskie
    public function odds()
    {
        return $this->hasMany(Odd::class, 'fixture_id');
    }

    public function getOdd(string $marketName, string $outcomeName)
    {
        return $this->odds
            ->where('market_name', $marketName)
            ->where('outcome_name', $outcomeName)
            ->first();
    }

    public function getWinOdd(string $type)
    {
        return $this->getOdd('Wynik', $type);
    }

    // Relacja do statystyk meczowych
    public function statistics()
    {
        return $this->hasOne(FixtureStatistic::class, 'fixture_id');
    }

    // Relacja do zakładów postawionych na ten mecz
    public function bets()
    {
        return $this->hasMany(Bet::class, 'fixture_id');
    }
}

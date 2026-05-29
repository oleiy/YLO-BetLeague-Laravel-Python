<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    // Pola, które można masowo przypisywać
    protected $fillable = [
        'api_id',
        'name',
        'short_name',
        'main_color',
        'text_color'
    ];

    // Poniższy podział pozwala na liczenie statystyk drużynowych
    // Mecze jako gospodarz
    public function homeFixtures()
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    // Mecze jako gość
    public function awayFixtures()
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}

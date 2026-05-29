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

    public function homeFixtures()
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures()
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    // Pola, które można masowo przypisywać
    protected $fillable = ['fixture_id', 'team_id', 'market_name', 'outcome_name', 'specifier', 'value'];

    // Relacja: Kurs przypisany jest do konkretnego meczu
    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function team()
{
    return $this->belongsTo(Team::class);
}

}

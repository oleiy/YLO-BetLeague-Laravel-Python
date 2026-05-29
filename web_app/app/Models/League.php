<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $fillable = ['api_id', 'name', 'country', 'is_active', 'priority', 'logo_path'];

    // Relacja: Liga posiada wiele meczów
    public function fixtures()
    {
        return $this->hasMany(Fixture::class, 'league_id');
    }
}

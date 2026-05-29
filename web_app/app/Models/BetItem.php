<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetItem extends Model
{
    protected $table = 'user_bet_items';

    protected $fillable = [
        'bet_id',
        'odd_id',
        'status'
    ];

    // Relacja odwrotna: Pozycja należy do zakładu
    public function bet(): BelongsTo
    {
        return $this->belongsTo(Bet::class);
    }

    // Relacja do kursu
    public function odd(): BelongsTo
    {
        return $this->belongsTo(Odd::class)->withDefault([
            'outcome_name' => 'Brak kursu',
            'value' => 0,
            'specifier' => null
        ]);
    }
}

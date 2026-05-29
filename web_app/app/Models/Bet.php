<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bet extends Model
{
    protected $table = 'user_bets';

    protected $fillable = [
        'user_id',
        'fixture_id',
        'total_odd',
        'stake',
        'potential_win',
        'analysis',
        'is_betbuilder',
        'status'
    ];

    // Relacja: Zakład należy do użytkownika
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacja: Zakład dotyczy konkretnego meczu
    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    // Relacja: Zakład składa się z wielu pozycji (szczególnie w Bet Builderze)
    public function items(): HasMany
    {
        return $this->hasMany(BetItem::class, 'bet_id');
    }
}

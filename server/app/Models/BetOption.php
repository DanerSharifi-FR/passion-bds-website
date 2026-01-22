<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Modèle BetOption.
 *
 * Option de pari pour un match.
 *
 * @property int $id
 * @property int $match_id
 * @property string $label
 * @property string $initial_odds
 * @property string $current_odds
 * @property int $pool_total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read BetMatch $match
 * @property-read Collection<int, BetBet> $bets
 */
class BetOption extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'label',
        'initial_odds',
        'current_odds',
        'pool_total',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'match_id' => 'integer',
        'initial_odds' => 'decimal:2',
        'current_odds' => 'decimal:2',
        'pool_total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Match auquel appartient l'option.
     *
     * @return BelongsTo<BetMatch, BetOption>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(BetMatch::class, 'match_id');
    }

    /**
     * Paris associés a cette option.
     *
     * @return HasMany<BetBet>
     */
    public function bets(): HasMany
    {
        return $this->hasMany(BetBet::class, 'option_id');
    }
}

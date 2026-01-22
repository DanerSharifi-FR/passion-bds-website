<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BetMatchStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Modèle BetMatch.
 *
 * Match supportant des paris.
 *
 * @property int $id
 * @property string $title
 * @property Carbon $bet_open_at
 * @property Carbon $match_start_at
 * @property Carbon $match_end_at
 * @property BetMatchStatus $status
 * @property bool $is_visible
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, BetOption> $options
 * @property-read Collection<int, BetBet> $bets
 */
class BetMatch extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'bet_open_at',
        'match_start_at',
        'match_end_at',
        'status',
        'is_visible',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'bet_open_at' => 'datetime',
        'match_start_at' => 'datetime',
        'match_end_at' => 'datetime',
        'status' => BetMatchStatus::class,
        'is_visible' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Options associées au match.
     *
     * @return HasMany<BetOption>
     */
    public function options(): HasMany
    {
        return $this->hasMany(BetOption::class, 'match_id');
    }

    /**
     * Paris associés au match.
     *
     * @return HasMany<BetBet>
     */
    public function bets(): HasMany
    {
        return $this->hasMany(BetBet::class, 'match_id');
    }

    /**
     * Indique si le match est visible côté utilisateur.
     *
     * @return bool
     */
    public function isUserVisible(): bool
    {
        return (bool) $this->is_visible;
    }

    /**
     * Indique si la fenêtre de pari est ouverte.
     *
     * @param Carbon $now
     * @return bool
     */
    public function isBettingWindowOpen(Carbon $now): bool
    {
        if ($this->status !== BetMatchStatus::OPEN) {
            return false;
        }

        return $now->gte($this->bet_open_at) && $now->lt($this->match_end_at);
    }
}

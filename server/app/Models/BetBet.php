<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BetBetStatus;
use App\Enums\BetResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle BetBet.
 *
 * Pari individuel.
 *
 * @property int $id
 * @property int $match_id
 * @property int $option_id
 * @property int $user_id
 * @property int $stake
 * @property string $odds_locked
 * @property BetBetStatus $status
 * @property BetResult|null $result
 * @property string|null $settled_batch_uuid
 * @property Carbon $editable_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read BetMatch $match
 * @property-read BetOption $option
 * @property-read User $user
 */
class BetBet extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'option_id',
        'user_id',
        'stake',
        'odds_locked',
        'status',
        'result',
        'settled_batch_uuid',
        'editable_until',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'match_id' => 'integer',
        'option_id' => 'integer',
        'user_id' => 'integer',
        'stake' => 'integer',
        'odds_locked' => 'decimal:2',
        'status' => BetBetStatus::class,
        'result' => BetResult::class,
        'settled_batch_uuid' => 'string',
        'editable_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Match du pari.
     *
     * @return BelongsTo<BetMatch, BetBet>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(BetMatch::class, 'match_id');
    }

    /**
     * Option choisie.
     *
     * @return BelongsTo<BetOption, BetBet>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(BetOption::class, 'option_id');
    }

    /**
     * Utilisateur du pari.
     *
     * @return BelongsTo<User, BetBet>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

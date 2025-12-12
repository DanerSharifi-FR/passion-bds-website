<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle AlloUsage.
 *
 * Représente une réservation d’allo par un étudiant sur un slot donné.
 *
 * @property int $id
 * @property int $allo_id
 * @property int $allo_slot_id
 * @property Carbon $slot_start_at
 * @property int $user_id
 * @property int $points_spent
 * @property string $status
 * @property int|null $handled_by_id
 * @property int|null $done_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $done_at
 * @property Carbon|null $cancelled_at
 *
 * Relations :
 * @property-read Allo $allo
 * @property-read AlloSlot $slot
 * @property-read User $user
 * @property-read User|null $handledBy
 * @property-read User|null $doneBy
 */
class AlloUsage extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'allo_usages';

    /**
     * Pas de colonne updated_at.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'allo_id',
        'allo_slot_id',
        'slot_start_at',
        'user_id',
        'points_spent',
        'status',
        'handled_by_id',
        'done_by_id',
        'created_at',
        'accepted_at',
        'done_at',
        'cancelled_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'allo_id' => 'integer',
        'allo_slot_id' => 'integer',
        'user_id' => 'integer',
        'points_spent' => 'integer',
        'handled_by_id' => 'integer',
        'done_by_id' => 'integer',
        'slot_start_at' => 'datetime',
        'created_at' => 'datetime',
        'accepted_at' => 'datetime',
        'done_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Allo concerné par cette réservation.
     *
     * @return BelongsTo<Allo, AlloUsage>
     */
    public function allo(): BelongsTo
    {
        return $this->belongsTo(Allo::class);
    }

    /**
     * Slot associé à cette réservation.
     *
     * @return BelongsTo<AlloSlot, AlloUsage>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(AlloSlot::class, 'allo_slot_id');
    }

    /**
     * Étudiant qui a réservé l’allo.
     *
     * @return BelongsTo<User, AlloUsage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin qui a pris en charge la demande.
     *
     * @return BelongsTo<User, AlloUsage>
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id');
    }

    /**
     * Admin qui a validé la réalisation de l’allo.
     *
     * @return BelongsTo<User, AlloUsage>
     */
    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by_id');
    }
}

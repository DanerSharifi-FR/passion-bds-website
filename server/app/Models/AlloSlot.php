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
 * Modèle AlloSlot.
 *
 * Représente un créneau horaire pour un allo donné.
 *
 * @property int $id
 * @property int $allo_id
 * @property Carbon $slot_start_at
 * @property Carbon $slot_end_at
 * @property string $status
 * @property Carbon|null $created_at
 *
 * Relations chargées dynamiquement :
 * @property-read Allo $allo
 * @property-read Collection<int, AlloUsage> $usages
 * @property-read int|null $usages_count
 */
class AlloSlot extends Model
{
    use HasFactory;

    /**
     * Nom de la table.
     *
     * @var string
     */
    protected $table = 'allo_slots';

    /**
     * Eloquent ne doit pas gérer de colonne updated_at
     * (la table ne l’a pas).
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * Attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'allo_id',
        'slot_start_at',
        'slot_end_at',
        'status',
    ];

    /**
     * Casts de types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allo_id' => 'integer',
        'slot_start_at' => 'datetime',
        'slot_end_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Allo auquel appartient ce slot.
     *
     * @return BelongsTo<Allo, AlloSlot>
     */
    public function allo(): BelongsTo
    {
        return $this->belongsTo(Allo::class);
    }

    /**
     * Usages (réservations) associés à ce slot.
     *
     * @return HasMany<AlloUsage>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(AlloUsage::class, 'allo_slot_id');
    }
}

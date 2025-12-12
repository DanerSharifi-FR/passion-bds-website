<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Modèle Allo.
 *
 * Représente un type d'allo (service) que les étudiants peuvent réserver sur des créneaux.
 *
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property int $points_cost
 * @property string $status
 * @property Carbon $window_start_at
 * @property Carbon $window_end_at
 * @property int $slot_duration_minutes
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Collection<int, AlloSlot> $slots
 * @property-read Collection<int, AlloUsage> $usages
 * @property-read Collection<int, User> $admins
 */
class Allo extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'allos';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'points_cost',
        'status',
        'window_start_at',
        'window_end_at',
        'slot_duration_minutes',
        'created_by_id',
        'updated_by_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'points_cost' => 'integer',
        'slot_duration_minutes' => 'integer',
        'window_start_at' => 'datetime',
        'window_end_at' => 'datetime',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Créateur de l’allo.
     *
     * @return BelongsTo<User, Allo>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Dernier utilisateur ayant modifié l’allo.
     *
     * @return BelongsTo<User, Allo>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Slots (créneaux) associés à cet allo.
     *
     * @return HasMany<AlloSlot>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(AlloSlot::class);
    }

    /**
     * Usages (réservations) de cet allo.
     *
     * @return HasMany<AlloUsage>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(AlloUsage::class);
    }

    /**
     * Admins responsables de cet allo.
     *
     * Relation many-to-many via la table allo_admins (allo_id, admin_id).
     *
     * @return BelongsToMany<User>
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'allo_admins', 'allo_id', 'admin_id');
    }
}

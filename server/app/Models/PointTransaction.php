<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Transaction de points pour un utilisateur.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property string $reason
 * @property string|null $context_type
 * @property int|null $context_id
 * @property int|null $created_by_id
 * @property Carbon|null $created_at
 *
 * Relations :
 * @property-read User|null $user
 * @property-read User|null $createdBy
 */
class PointTransaction extends Model
{
    /**
     * Cette table n'a pas de colonne updated_at.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'point_transactions';

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'amount' => 'integer',
        'context_id' => 'integer',
        'created_by_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Étudiant concerné par la transaction.
     *
     * @return BelongsTo<User, PointTransaction>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin ayant créé la transaction.
     *
     * @return BelongsTo<User, PointTransaction>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

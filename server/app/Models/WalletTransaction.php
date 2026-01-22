<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle WalletTransaction.
 *
 * Transaction de credits.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property WalletTransactionType $type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 */
class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'reference_type',
        'reference_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'integer',
        'type' => WalletTransactionType::class,
        'reference_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Utilisateur associe a la transaction.
     *
     * @return BelongsTo<User, WalletTransaction>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

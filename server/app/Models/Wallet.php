<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle Wallet.
 *
 * Portefeuille de credits par utilisateur.
 *
 * @property int $user_id
 * @property int $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 */
class Wallet extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'int';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'balance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Utilisateur du portefeuille.
     *
     * @return BelongsTo<User, Wallet>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

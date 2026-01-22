<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Log d'actions admin sur les paris.
 *
 * @property int $id
 * @property int $admin_id
 * @property int|null $bet_id
 * @property int|null $match_id
 * @property string $action
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BetAdminAction extends Model
{
    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'admin_id' => 'integer',
        'bet_id' => 'integer',
        'match_id' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

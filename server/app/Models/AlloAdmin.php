<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlloAdmin extends Model
{
    use HasFactory;

    protected $table = 'allo_admins';

    /**
     * La table a uniquement un created_at (pas de updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'allo_id',
        'admin_id',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'allo_id' => 'integer',
        'admin_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Allo auquel cet admin est assigné.
     *
     * @return BelongsTo<Allo, AlloAdmin>
     */
    public function allo(): BelongsTo
    {
        return $this->belongsTo(Allo::class, 'allo_id');
    }

    /**
     * Utilisateur admin assigné à cet allo.
     *
     * @return BelongsTo<User, AlloAdmin>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

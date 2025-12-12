<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'university_email',
        'display_name',
        'avatar_url',
        'is_active',
        'last_login_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Roles granted to the user.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Check if the user has at least one of the given roles.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if the user has at least one of the given roles.
     *
     * @param  string|array<int, string>  $roles  Role name or list of role names.
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->whereIn('name', $roles)->isNotEmpty();
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if the user has all of the given roles.
     *
     * @param  array<int, string>  $roles  List of role names.
     */
    public function hasAllRoles(array $roles): bool
    {
        /** @var Collection<int, string> $userRoles */
        $userRoles = $this->roles->pluck('name');

        if ($userRoles->contains('ROLE_SUPER_ADMIN')) {
            return true;
        }

        foreach ($roles as $roleName) {
            if (! $userRoles->contains($roleName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Indique si l'utilisateur est super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    public static function displayNameFromUniversityEmail(string $email): string
    {
        $email = mb_strtolower(trim($email));
        $local = explode('@', $email, 2)[0] ?? '';
        [$first, $last] = array_pad(explode('.', $local, 2), 2, '');

        $first = self::titleizeEmailPart($first);
        $last  = self::titleizeEmailPart($last);

        return trim($first . ' ' . $last);
    }

    private static function titleizeEmailPart(string $part): string
    {
        // collapse --- into -
        $part = preg_replace('/-+/', '-', $part);
        $chunks = array_values(array_filter(explode('-', $part), fn ($s) => $s !== ''));

        $chunks = array_map(
            fn ($s) => mb_convert_case($s, MB_CASE_TITLE, 'UTF-8'),
            $chunks
        );

        // keep hyphens in names
        return implode('-', $chunks);
    }
}

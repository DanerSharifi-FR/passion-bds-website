<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class UserRoleService
{
    /**
     * Synchronise la liste des rôles d’un utilisateur à partir d’une liste de noms.
     *
     * - Ne crée pas de nouveaux rôles : uniquement ceux déjà présents en base.
     * - Supprime les rôles non listés de l’utilisateur.
     *
     * @param User $user       Utilisateur cible.
     * @param  array<int, string>        $roleNames  Liste des noms de rôles (ex: ["ROLE_GAMEMASTER", "ROLE_BLOGGER"]).
     * @return void
     */
    public function syncUserRoles(User $user, array $roleNames): void
    {
        if ($roleNames === []) {
            $user->roles()->sync([]);

            return;
        }

        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->whereIn('name', $roleNames)
            ->get();

        /** @var array<int, int> $roleIds */
        $roleIds = $roles->pluck('id')->all();

        $user->roles()->sync($roleIds);
    }

    /**
     * Ajoute un rôle à un utilisateur (si le rôle existe et n’est pas déjà présent).
     *
     * @param User $user
     * @param  string            $roleName
     * @return void
     */
    public function grantRole(User $user, string $roleName): void
    {
        /** @var Role|null $role */
        $role = Role::query()
            ->where('name', $roleName)
            ->first();

        if ($role === null) {
            return;
        }

        if ($user->roles()->where('roles.id', $role->id)->exists()) {
            return;
        }

        $user->roles()->attach($role->id);
    }

    /**
     * Retire un rôle à un utilisateur (si l’utilisateur le possède).
     *
     * @param User $user
     * @param  string            $roleName
     * @return void
     */
    public function revokeRole(User $user, string $roleName): void
    {
        /** @var Role|null $role */
        $role = Role::query()
            ->where('name', $roleName)
            ->first();

        if ($role === null) {
            return;
        }

        $user->roles()->detach($role->id);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUsersApiController extends Controller
{
    private const ALLOWED_ROLES = [
        'ROLE_SUPER_ADMIN',
        'ROLE_BLOGGER',
        'ROLE_GAMEMASTER',
        'ROLE_SHOP',
        'ROLE_TEAM',
    ];

    private ?array $actorRolesCache = null;

    private function actorRoleNames(): array
    {
        if ($this->actorRolesCache !== null) {
            return $this->actorRolesCache;
        }

        $actorId = auth()->id();
        if (!$actorId) {
            return $this->actorRolesCache = [];
        }

        return $this->actorRolesCache = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $actorId)
            ->pluck('roles.name')
            ->all();
    }

    private function actorIsSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->actorRoleNames(), true);
    }

    private function actorIsGameMaster(): bool
    {
        return in_array('ROLE_GAMEMASTER', $this->actorRoleNames(), true);
    }

    private function ensureCanAccessList(): void
    {
        if (!auth()->check()) abort(401);

        if (!$this->actorIsSuperAdmin() && !$this->actorIsGameMaster()) {
            abort(403, 'Accès interdit.');
        }
    }

    private function ensureSuperAdmin(): void
    {
        if (!auth()->check()) abort(401);

        if (!$this->actorIsSuperAdmin()) {
            abort(403, 'Réservé au SUPER ADMIN.');
        }
    }

    private function userHasAnyAdminRole(int $userId): bool
    {
        // Dans ton modèle: étudiant = 0 rôle en base. Donc "admin" = au moins 1 ligne user_roles.
        return DB::table('user_roles')->where('user_id', $userId)->exists();
    }

    public function index(Request $request)
    {
        $this->ensureCanAccessList();

        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));

        $actorIsSuperAdmin = $this->actorIsSuperAdmin();

        $users = User::query()
            ->select([
                'users.id',
                'users.university_email',
                'users.display_name',
                'users.is_active',
                'users.created_at',
                'users.updated_at',
            ])
            ->with(['roles:id,name'])
            ->addSelect([
                'points' => DB::table('point_transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('point_transactions.user_id', 'users.id'),
            ]);

        // ✅ Gamemaster: ne voit AUCUN admin (donc uniquement étudiants)
        if (!$actorIsSuperAdmin) {
            $users->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('user_roles')
                    ->whereColumn('user_roles.user_id', 'users.id');
            });
        }

        if ($search !== '') {
            $users->where(function ($q) use ($search) {
                $q->where('users.university_email', 'like', '%' . $search . '%')
                    ->orWhere('users.display_name', 'like', '%' . $search . '%');
            });
        }

        if ($role !== '') {
            if ($role === 'ADMIN') {
                $users->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('user_roles')
                        ->whereColumn('user_roles.user_id', 'users.id');
                });
            } elseif (in_array($role, self::ALLOWED_ROLES, true)) {
                $users->whereHas('roles', fn ($q) => $q->where('name', $role));
            }
        }

        $rows = $users
            ->orderByDesc('users.id')
            ->limit(200)
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'email' => $u->university_email,
                    'display_name' => $u->display_name,
                    'is_active' => (bool) $u->is_active,
                    'roles' => $u->roles->pluck('name')->values()->all(),
                    'points' => (int) ($u->points ?? 0),
                ];
            });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function store(CreateAdminUserRequest $request)
    {
        // ✅ Seul SUPER_ADMIN peut créer (sinon un GM peut “ajouter des admins”)
        $this->ensureSuperAdmin();

        $email = mb_strtolower(trim($request->string('email')->toString()));
        $displayNameInput = trim((string) $request->validated('display_name', ''));

        // roles can be null/absent => student
        $roles = $request->validated('roles', []);
        if (!is_array($roles)) {
            $roles = [];
        }

        $roles = array_values(array_unique(array_filter(
            $roles,
            fn ($r) => in_array($r, self::ALLOWED_ROLES, true) && $r !== 'ROLE_SUPER_ADMIN'
        )));

        $user = DB::transaction(function () use ($email, $roles, $displayNameInput) {
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            if (!$user) {
                $user = new User();
                $user->university_email = $email;

                $name = $displayNameInput;
                if (mb_strlen($name) < 2) {
                    $name = $this->displayNameFromEmail($email);
                }

                $user->display_name = $name;
                $user->avatar_url = null;
                $user->is_active = true;
                $user->save();
            } elseif (!$user->display_name) {
                $name = $displayNameInput;
                if (mb_strlen($name) < 2) {
                    $name = $this->displayNameFromEmail($email);
                }
                $user->display_name = $name;
                $user->save();
            }

            // Resolve role IDs for requested roles
            $roleIds = DB::table('roles')
                ->whereIn('name', $roles)
                ->pluck('id')
                ->all();

            // Protect SUPER_ADMIN from being removed by this endpoint
            $protectedRoleIds = DB::table('roles')
                ->where('name', 'ROLE_SUPER_ADMIN')
                ->pluck('id')
                ->all();

            DB::table('user_roles')
                ->where('user_id', $user->id)
                ->whereNotIn('role_id', array_values(array_unique(array_merge($roleIds, $protectedRoleIds))))
                ->delete();

            foreach ($roleIds as $rid) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $rid],
                    []
                );
            }

            $user->load('roles:id,name');

            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->university_email,
                'display_name' => $user->display_name,
                'roles' => $user->roles->pluck('name')->values()->all(),
            ],
        ], 201);
    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        $actorId = auth()->id();
        if (!$actorId) {
            abort(401);
        }

        $actorRoles = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $actorId)
            ->pluck('roles.name')
            ->all();

        $actorIsSuperAdmin = in_array('ROLE_SUPER_ADMIN', $actorRoles, true);
        $actorIsGameMaster = in_array('ROLE_GAMEMASTER', $actorRoles, true);

        if (!$actorIsSuperAdmin && !$actorIsGameMaster) {
            abort(403, 'Accès interdit.');
        }

        $payload = $request->validated();

        $displayNameProvided = array_key_exists('display_name', $payload);
        $displayName = $displayNameProvided ? trim((string) $payload['display_name']) : null;

        $rolesProvided = array_key_exists('roles', $payload);
        $roles = $rolesProvided ? $payload['roles'] : [];
        if (!is_array($roles)) {
            $roles = [];
        }

        // Nettoyage rôles demandés (SUPER_ADMIN ne peut jamais être attribué via cet endpoint)
        $roles = array_values(array_unique(array_filter(
            $roles,
            fn ($r) => in_array($r, self::ALLOWED_ROLES, true) && $r !== 'ROLE_SUPER_ADMIN'
        )));

        $updated = DB::transaction(function () use (
            $user,
            $actorIsSuperAdmin,
            $displayNameProvided,
            $displayName,
            $rolesProvided,
            $roles
        ) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $targetHasAnyRole = DB::table('user_roles')
                ->where('user_id', $lockedUser->id)
                ->exists();

            // ✅ Gamemaster: uniquement étudiants (0 rôles) + jamais de modif rôles
            if (!$actorIsSuperAdmin) {
                if ($targetHasAnyRole) {
                    abort(403, 'Non autorisé: tu ne peux modifier que des étudiants.');
                }

                if ($rolesProvided && count($roles) > 0) {
                    abort(403, 'Non autorisé: tu ne peux pas attribuer des rôles.');
                }
            }

            // ✅ Update display name (si fourni)
            if ($displayNameProvided && $displayName !== null && $displayName !== '' && $displayName !== (string) $lockedUser->display_name) {
                $lockedUser->display_name = $displayName;
                $lockedUser->save();
            }

            // ✅ Update roles (SUPER_ADMIN uniquement, et seulement si "roles" est présent dans la requête)
            if ($actorIsSuperAdmin && $rolesProvided) {
                $superRoleId = DB::table('roles')->where('name', 'ROLE_SUPER_ADMIN')->value('id');
                $keepSuperAdmin = $superRoleId
                    ? DB::table('user_roles')->where('user_id', $lockedUser->id)->where('role_id', $superRoleId)->exists()
                    : false;

                $roleIds = DB::table('roles')
                    ->whereIn('name', $roles)
                    ->pluck('id')
                    ->all();

                if ($keepSuperAdmin && $superRoleId) {
                    $roleIds[] = $superRoleId;
                }
                $roleIds = array_values(array_unique($roleIds));

                if (count($roleIds) === 0) {
                    DB::table('user_roles')->where('user_id', $lockedUser->id)->delete();
                } else {
                    DB::table('user_roles')
                        ->where('user_id', $lockedUser->id)
                        ->whereNotIn('role_id', $roleIds)
                        ->delete();
                }

                foreach ($roleIds as $rid) {
                    DB::table('user_roles')->updateOrInsert(
                        ['user_id' => $lockedUser->id, 'role_id' => $rid],
                        []
                    );
                }
            }

            $lockedUser->load('roles:id,name');

            return $lockedUser;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $updated->id,
                'email' => $updated->university_email,
                'display_name' => $updated->display_name,
                'roles' => $updated->roles->pluck('name')->values()->all(),
            ],
        ]);
    }

    public function destroy(User $user)
    {
        // GM peut accéder au listing, mais on ne lui laisse pas toucher aux admins.
        $this->ensureCanAccessList();

        $actorId = auth()->id();

        if ($actorId && (int) $actorId === (int) $user->id) {
            return response()->json([
                'message' => "Tu peux pas te supprimer toi-même.",
                'errors' => ['user' => ["Tu peux pas te supprimer toi-même."]],
            ], 422);
        }

        if (!$this->actorIsSuperAdmin()) {
            if ($this->userHasAnyAdminRole((int) $user->id)) {
                return response()->json([
                    'message' => "Non autorisé: tu ne peux pas supprimer un admin.",
                    'errors' => ['user' => ["Non autorisé: tu ne peux pas supprimer un admin."]],
                ], 403);
            }
        }

        $isSuperAdmin = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $user->id)
            ->where('roles.name', 'ROLE_SUPER_ADMIN')
            ->exists();

        if ($isSuperAdmin) {
            return response()->json([
                'message' => "Impossible de supprimer un SUPER ADMIN.",
                'errors' => ['user' => ["Impossible de supprimer un SUPER ADMIN."]],
            ], 403);
        }

        $blockers = [];

        if (DB::table('point_transactions')->where('user_id', $user->id)->exists()) $blockers[] = 'point_transactions.user_id';
        if (DB::table('point_transactions')->where('created_by_id', $user->id)->exists()) $blockers[] = 'point_transactions.created_by_id';

        if (DB::table('allos')->where('created_by_id', $user->id)->exists()) $blockers[] = 'allos.created_by_id';
        if (DB::table('allos')->where('updated_by_id', $user->id)->exists()) $blockers[] = 'allos.updated_by_id';

        if (DB::table('allo_admins')->where('admin_id', $user->id)->exists()) $blockers[] = 'allo_admins.admin_id';

        if (DB::table('allo_usages')->where('user_id', $user->id)->exists()) $blockers[] = 'allo_usages.user_id';
        if (DB::table('allo_usages')->where('handled_by_id', $user->id)->exists()) $blockers[] = 'allo_usages.handled_by_id';
        if (DB::table('allo_usages')->where('done_by_id', $user->id)->exists()) $blockers[] = 'allo_usages.done_by_id';

        if (count($blockers) > 0) {
            return response()->json([
                'message' => "Suppression impossible: l'utilisateur est référencé ailleurs.",
                'errors' => ['user' => $blockers],
            ], 409);
        }

        DB::transaction(function () use ($user) {
            DB::table('user_roles')->where('user_id', $user->id)->delete();
            DB::table('login_codes')->where('user_id', $user->id)->delete();
            User::whereKey($user->id)->delete();
        });

        return response()->json(['success' => true]);
    }

    private function displayNameFromEmail(string $email): string
    {
        $local = explode('@', $email)[0] ?? '';
        $parts = explode('.', $local);

        $fmt = function (string $s): string {
            $s = trim($s);
            if ($s === '') return '';
            $s = str_replace(['-', '_'], ' ', $s);
            $words = preg_split('/\s+/', $s) ?: [];
            $words = array_map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)) . mb_strtolower(mb_substr($w, 1)), $words);
            return trim(implode(' ', $words));
        };

        $first = $fmt($parts[0] ?? '');
        $last  = $fmt($parts[1] ?? '');

        $name = trim($first . ' ' . $last);
        return $name !== '' ? $name : 'Utilisateur';
    }
}

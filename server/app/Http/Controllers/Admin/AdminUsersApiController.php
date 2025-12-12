<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAdminUserRequest;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\User;
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

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));

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
        $email = mb_strtolower(trim($request->string('email')->toString()));

        // roles can be null/absent => student
        $roles = $request->validated('roles', []);
        if (!is_array($roles)) {
            $roles = [];
        }

        $roles = array_values(array_unique(array_filter(
            $roles,
            fn ($r) => in_array($r, self::ALLOWED_ROLES, true) && $r !== 'ROLE_SUPER_ADMIN'
        )));

        $user = DB::transaction(function () use ($email, $roles) {
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            if (!$user) {
                $user = new User();
                $user->university_email = $email;
                $user->display_name = $this->displayNameFromEmail($email);
                $user->avatar_url = null;
                $user->is_active = true;
                $user->save();
            } elseif (!$user->display_name) {
                $user->display_name = $this->displayNameFromEmail($email);
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

            // Delete everything not desired and not protected
            DB::table('user_roles')
                ->where('user_id', $user->id)
                ->whereNotIn('role_id', array_values(array_unique(array_merge($roleIds, $protectedRoleIds))))
                ->delete();

            // Insert desired roles (can be empty => student)
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

    public function updateRoles(UpdateUserRolesRequest $request, User $user)
    {
        $roles = $request->validated('roles', []);
        if (!is_array($roles)) {
            $roles = [];
        }

        $roles = array_values(array_unique(array_filter(
            $roles,
            fn ($r) => in_array($r, self::ALLOWED_ROLES, true) && $r !== 'ROLE_SUPER_ADMIN'
        )));

        $updated = DB::transaction(function () use ($user, $roles) {

            // Lock the user row properly
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            // Check if SUPER_ADMIN is currently present
            $superRoleId = DB::table('roles')->where('name', 'ROLE_SUPER_ADMIN')->value('id');
            $keepSuperAdmin = $superRoleId
                ? DB::table('user_roles')->where('user_id', $lockedUser->id)->where('role_id', $superRoleId)->exists()
                : false;

            // Resolve ids for requested roles (can be empty)
            $roleIds = DB::table('roles')
                ->whereIn('name', $roles)
                ->pluck('id')
                ->all();

            // Desired final set: requested roles + (optional) protected super admin
            if ($keepSuperAdmin && $superRoleId) {
                $roleIds[] = $superRoleId;
            }
            $roleIds = array_values(array_unique($roleIds));

            // Replace roles: delete everything not in desired set
            if (count($roleIds) === 0) {
                // Student => remove all roles EXCEPT super admin doesn't exist here anyway
                DB::table('user_roles')->where('user_id', $lockedUser->id)->delete();
            } else {
                DB::table('user_roles')
                    ->where('user_id', $lockedUser->id)
                    ->whereNotIn('role_id', $roleIds)
                    ->delete();
            }

            // Insert missing desired roles
            foreach ($roleIds as $rid) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $lockedUser->id, 'role_id' => $rid],
                    []
                );
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
        $actorId = auth()->id();

        if ($actorId && (int) $actorId === (int) $user->id) {
            return response()->json([
                'message' => "Tu peux pas te supprimer toi-même.",
                'errors' => ['user' => ["Tu peux pas te supprimer toi-même."]],
            ], 422);
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


        // if (DB::table('media_items')->where('uploader_id', $user->id)->exists()) $blockers[] = 'media_items.uploader_id';

        // if (DB::table('team_members')->where('user_id', $user->id)->exists()) $blockers[] = 'team_members.user_id';

        // if (DB::table('audit_logs')->where('actor_id', $user->id)->exists()) $blockers[] = 'audit_logs.actor_id';

        // if (DB::table('challenges')->where('created_by_id', $user->id)->exists()) $blockers[] = 'challenges.created_by_id';
        // if (DB::table('challenges')->where('updated_by_id', $user->id)->exists()) $blockers[] = 'challenges.updated_by_id';

        // if (DB::table('challenge_attempts')->where('user_id', $user->id)->exists()) $blockers[] = 'challenge_attempts.user_id';
        // if (DB::table('challenge_attempts')->where('reviewed_by_id', $user->id)->exists()) $blockers[] = 'challenge_attempts.reviewed_by_id';

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

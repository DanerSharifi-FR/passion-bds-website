<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivitiesApiController extends Controller
{
    public function index(Request $request)
    {
        $actorId = (int) auth()->id();
        $isSuperAdmin = $this->userHasRole($actorId, 'ROLE_SUPER_ADMIN');

        $search = trim((string) $request->query('q', ''));

        $query = DB::table('activities as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.created_by_id')
            ->select([
                'a.id',
                'a.title',
                'a.slug',
                'a.points_label',
                'a.mode',
                'a.is_active',
                'a.created_at',
                'a.updated_at',
                'a.created_by_id',
                DB::raw('COALESCE(u.display_name, u.university_email) as created_by_name'),
            ])
            ->selectSub(function ($sub) {
                $sub->from('activity_admins as aa')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('aa.activity_id', 'a.id');
            }, 'admins_count')
            ->selectSub(function ($sub) {
                $sub->from('activity_participants as ap')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('ap.activity_id', 'a.id');
            }, 'participants_count')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('a.title', 'like', "%{$search}%")
                        ->orWhere('a.slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('a.created_at');

        if (!$isSuperAdmin) {
            $query->whereExists(function ($sub) use ($actorId) {
                $sub->selectRaw('1')
                    ->from('activity_admins as aa')
                    ->whereColumn('aa.activity_id', 'a.id')
                    ->where('aa.admin_id', $actorId);
            });
        }

        $activities = $query->get()->map(function ($row) use ($actorId, $isSuperAdmin) {
            $row->can_manage = $isSuperAdmin || $this->isActivityAdmin($actorId, (int) $row->id);
            return $row;
        });

        return response()->json(['data' => $activities]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'min:2', 'max:255', 'unique:activities,title'],
            'points_label' => ['required', 'string', 'min:1', 'max:50'],
            'mode'         => ['required', Rule::in(['INDIVIDUAL', 'TEAM'])],
            'is_active'    => ['nullable', 'boolean'],
        ], [
            'title.unique' => "Ce nom d’activité existe déjà.",
        ]);

        try {
            $activity = Activity::create([
                'title'         => $validated['title'],
                'slug'          => Str::slug($validated['title']),
                'points_label'  => $validated['points_label'],
                'mode'          => $validated['mode'],
                'is_active'     => (bool)($validated['is_active'] ?? true),
                'created_by_id' => Auth::id(),
            ]);
        } catch (QueryException $e) {
            // safety net (race condition)
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json([
                    'message' => "Ce nom d’activité existe déjà.",
                    'errors'  => ['title' => ["Ce nom d’activité existe déjà."]],
                ], 422);
            }
            throw $e;
        }

        return response()->json(['data' => $activity], 201);
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'min:2', 'max:255', Rule::unique('activities', 'title')->ignore($activity->id)],
            'points_label' => ['required', 'string', 'min:1', 'max:50'],
            'mode'         => ['required', Rule::in(['INDIVIDUAL', 'TEAM'])],
            'is_active'    => ['nullable', 'boolean'],
        ], [
            'title.unique' => "Ce nom d’activité existe déjà.",
        ]);

        $activity->update([
            'title'        => $validated['title'],
            'slug'         => Str::slug($validated['title']),
            'points_label' => $validated['points_label'],
            'mode'         => $validated['mode'],
            'is_active'    => (bool)($validated['is_active'] ?? $activity->is_active),
        ]);

        return response()->json(['data' => $activity]);
    }
    public function listAdmins(Activity $activity)
    {
        $admins = DB::table('activity_admins as aa')
            ->join('users as u', 'u.id', '=', 'aa.admin_id')
            ->where('aa.activity_id', $activity->id)

            // must have ROLE_GAMEMASTER
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('user_roles as ur')
                    ->join('roles as r', 'r.id', '=', 'ur.role_id')
                    ->whereColumn('ur.user_id', 'u.id')
                    ->where('r.name', 'ROLE_GAMEMASTER');
            })

            // must NOT have ROLE_SUPER_ADMIN
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('user_roles as ur2')
                    ->join('roles as r2', 'r2.id', '=', 'ur2.role_id')
                    ->whereColumn('ur2.user_id', 'u.id')
                    ->where('r2.name', 'ROLE_SUPER_ADMIN');
            })

            ->select([
                'u.id',
                DB::raw('COALESCE(u.display_name, u.university_email) as name'),
                DB::raw('u.university_email as email'),
            ])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $admins]);
    }


    public function addAdmin(Request $request, int $activity)
    {
        $actorId = (int) auth()->id();
        $this->assertCanManageActivity($activity);

        $validated = $request->validate([
            'admin_id' => ['required', 'integer', 'min:1'],
        ]);

        $adminId = (int) $validated['admin_id'];

        // Pas d’auto-ajout inutile
        if ($adminId === $actorId) {
            return response()->json(['data' => 'ok']);
        }

        // Sécurité: uniquement ROLE_GAMEMASTER, jamais SUPER_ADMIN
        if (!$this->userHasRole($adminId, 'ROLE_GAMEMASTER') || $this->userHasRole($adminId, 'ROLE_SUPER_ADMIN')) {
            return response()->json(['message' => 'Utilisateur non invitable (doit être GAMEMASTER et pas SUPER_ADMIN).'], 422);
        }

        // L’activité doit exister
        $exists = DB::table('activities')->where('id', $activity)->exists();
        if (!$exists) abort(404);

        DB::table('activity_admins')->insertOrIgnore([
            'activity_id' => $activity,
            'admin_id'    => $adminId,
            'created_at'  => now(),
        ]);

        return response()->json(['data' => 'ok'], 201);
    }

    public function removeAdmin(Request $request, int $activity, int $adminId)
    {
        $this->assertCanManageActivity($activity);

        // Ne jamais laisser une activité sans admin
        $adminsCount = (int) DB::table('activity_admins')
            ->where('activity_id', $activity)
            ->count();

        if ($adminsCount <= 1) {
            return response()->json(['message' => 'Impossible de supprimer le dernier admin de l’activité.'], 422);
        }

        DB::table('activity_admins')
            ->where('activity_id', $activity)
            ->where('admin_id', $adminId)
            ->delete();

        return response()->json(['data' => 'ok']);
    }

    public function invitableGamemasters(Request $request, int $activity)
    {
        $this->assertCanManageActivity($activity);

        $q = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 10);
        if ($limit < 1) $limit = 10;
        if ($limit > 25) $limit = 25;

        // Users who are ROLE_GAMEMASTER
        $gmUserIdsQuery = DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('r.name', 'ROLE_GAMEMASTER')
            ->select('ur.user_id');

        // Users who are ROLE_SUPER_ADMIN (must be excluded)
        $superUserIdsQuery = DB::table('user_roles as ur2')
            ->join('roles as r2', 'r2.id', '=', 'ur2.role_id')
            ->where('r2.name', 'ROLE_SUPER_ADMIN')
            ->select('ur2.user_id');

        $alreadyAdminsQuery = DB::table('activity_admins')
            ->where('activity_id', $activity)
            ->select('admin_id');

        $users = DB::table('users as u')
            ->select([
                'u.id',
                'u.university_email as email',
                'u.display_name',
            ])
            ->whereIn('u.id', $gmUserIdsQuery)
            ->whereNotIn('u.id', $superUserIdsQuery)
            ->whereNotIn('u.id', $alreadyAdminsQuery)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('u.university_email', 'like', "%{$q}%")
                        ->orWhere('u.display_name', 'like', "%{$q}%");
                });
            })
            ->orderBy('u.display_name')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->name = trim((string) ($row->display_name ?: $row->email));
                return $row;
            });

        return response()->json(['data' => $users]);
    }

    // -------------------------
    // Helpers
    // -------------------------

    private function assertCanManageActivity(int $activityId): void
    {
        $actorId = (int) auth()->id();
        if ($actorId <= 0) abort(401);

        if ($this->userHasRole($actorId, 'ROLE_SUPER_ADMIN')) return;

        if (!$this->isActivityAdmin($actorId, $activityId)) {
            abort(403);
        }
    }

    private function isActivityAdmin(int $userId, int $activityId): bool
    {
        return DB::table('activity_admins')
            ->where('activity_id', $activityId)
            ->where('admin_id', $userId)
            ->exists();
    }

    private function userHasRole(int $userId, string $roleName): bool
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.name', $roleName)
            ->exists();
    }

    private function uniqueSlugForActivities(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'activity';

        $slug = $base;
        $i = 2;

        while (DB::table('activities')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}

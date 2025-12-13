<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActivityTeamsApiController extends Controller
{
    public function index(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (($activity->mode ?? 'INDIVIDUAL') !== 'TEAM') {
            return response()->json(['data' => []]);
        }

        $teams = DB::table('activity_teams as t')
            ->leftJoin('activity_team_members as m', function ($join) {
                $join->on('m.team_id', '=', 't.id');
            })
            ->leftJoin('point_transactions as pt', function ($join) {
                $join->on('pt.user_id', '=', 'm.user_id');
                $join->on('pt.activity_id', '=', 't.activity_id');
            })
            ->where('t.activity_id', (int) $activity->id)
            ->groupBy('t.id', 't.title', 't.activity_id')
            ->orderBy('t.created_at', 'asc')
            ->select([
                't.id',
                't.title',
                DB::raw('COUNT(DISTINCT m.user_id) as members_count'),
                DB::raw('COALESCE(SUM(pt.amount), 0) as points_total'),
            ])
            ->get();

        return response()->json(['data' => $teams]);
    }

    public function store(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (($activity->mode ?? 'INDIVIDUAL') !== 'TEAM') {
            return response()->json(['message' => "Cette activité n'est pas en mode équipes."], 422);
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('activity_teams', 'title')->where(fn ($q) => $q->where('activity_id', (int) $activity->id)),
            ],
        ]);

        $title = trim((string) $validated['title']);

        $id = DB::table('activity_teams')->insertGetId([
            'activity_id' => (int) $activity->id,
            'title' => $title,
            'created_by_id' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => (int) $id,
                'title' => $title,
                'members_count' => 0,
                'points_total' => 0,
            ],
        ], 201);
    }

    public function destroy(Request $request, Activity $activity, int $teamId)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $deleted = DB::table('activity_teams')
            ->where('activity_id', (int) $activity->id)
            ->where('id', (int) $teamId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Équipe introuvable.'], 404);
        }

        return response()->json(['message' => 'OK']);
    }

    private function canManageActivity(int $actorId, int $activityId, int $createdById): bool
    {
        if ($this->userHasRole($actorId, 'ROLE_SUPER_ADMIN')) return true;
        if ($createdById > 0 && $actorId === $createdById) return true;

        return DB::table('activity_admins')
            ->where('activity_id', $activityId)
            ->where('admin_id', $actorId)
            ->exists();
    }

    private function userHasRole(int $userId, string $roleName): bool
    {
        return DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->where('roles.name', $roleName)
            ->exists();
    }
}

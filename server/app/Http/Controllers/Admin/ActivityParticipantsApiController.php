<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityParticipantsApiController extends Controller
{
    public function index(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $q = trim((string) $request->query('q', ''));
        $teamFilterRaw = trim((string) $request->query('team_id', ''));

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $offset = ($page - 1) * $perPage;

        $pointsSub = DB::table('point_transactions')
            ->select('user_id', DB::raw('SUM(amount) as points_total'))
            ->where('activity_id', (int) $activity->id)
            ->groupBy('user_id');

        $isTeamMode = ($activity->mode ?? 'INDIVIDUAL') === 'TEAM';

        $teamSub = null;
        if ($isTeamMode) {
            $teamSub = DB::table('activity_team_members as atm')
                ->join('activity_teams as t', 't.id', '=', 'atm.team_id')
                ->where('t.activity_id', (int) $activity->id)
                ->select([
                    'atm.user_id',
                    't.id as team_id',
                    't.title as team_title',
                ]);
        }

        $base = DB::table('activity_participants as ap')
            ->join('users as u', 'u.id', '=', 'ap.user_id')
            ->leftJoinSub($pointsSub, 'pts', function ($join) {
                $join->on('pts.user_id', '=', 'u.id');
            })
            ->where('ap.activity_id', (int) $activity->id);

        if ($isTeamMode && $teamSub) {
            $base->leftJoinSub($teamSub, 'tm', function ($join) {
                $join->on('tm.user_id', '=', 'u.id');
            });
        }

        // Filter by team (TEAM mode only)
        if ($isTeamMode && $teamFilterRaw !== '') {
            if ($teamFilterRaw === 'none') {
                $base->whereNull('tm.team_id');
            } elseif (ctype_digit($teamFilterRaw)) {
                $base->where('tm.team_id', (int) $teamFilterRaw);
            }
        }

        if ($q !== '') {
            $base->where(function ($qq) use ($q) {
                $qq->where('u.display_name', 'like', '%' . $q . '%')
                    ->orWhere('u.university_email', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $base)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));

        $rows = (clone $base)
            ->orderByRaw("COALESCE(u.display_name, '') ASC")
            ->offset($offset)
            ->limit($perPage)
            ->select(array_values(array_filter([
                'u.id as user_id',
                'u.display_name as name',
                'u.university_email as email',
                DB::raw('COALESCE(pts.points_total, 0) as points'),
                $isTeamMode ? 'tm.team_id as team_id' : null,
                $isTeamMode ? 'tm.team_title as team_title' : null,
            ])))
            ->get();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => min($page, $lastPage),
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    /**
     * IMPORTANT: this is used by the "Ajouter joueur" modal.
     * It must search in USERS (students), not in activity_participants.
     */
    public function search(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $pointsSub = DB::table('point_transactions')
            ->select('user_id', DB::raw('SUM(amount) as points_total'))
            ->where('activity_id', (int) $activity->id)
            ->groupBy('user_id');

        // Students only = users that have NO rows in user_roles
        $rows = DB::table('users as u')
            ->leftJoinSub($pointsSub, 'pts', function ($join) {
                $join->on('pts.user_id', '=', 'u.id');
            })
            ->where(function ($q) use ($term) {
                $q->where('u.display_name', 'like', '%' . $term . '%')
                    ->orWhere('u.university_email', 'like', '%' . $term . '%');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('user_roles as ur')
                    ->whereColumn('ur.user_id', 'u.id');
            })
            ->whereNotExists(function ($q) use ($activity) {
                $q->select(DB::raw(1))
                    ->from('activity_participants as ap')
                    ->whereColumn('ap.user_id', 'u.id')
                    ->where('ap.activity_id', (int) $activity->id);
            })
            ->orderByRaw("COALESCE(u.display_name, '') ASC")
            ->limit(20)
            ->select([
                'u.id',
                'u.display_name as name',
                'u.university_email as email',
                DB::raw('COALESCE(pts.points_total, 0) as points'),
            ])
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * Add participant OR update his team assignment (same endpoint).
     */
    public function store(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'team_id' => ['nullable', 'integer'],
        ]);

        $userId = (int) $validated['user_id'];
        $teamId = isset($validated['team_id']) && $validated['team_id'] !== null ? (int) $validated['team_id'] : null;

        // Students only
        if ($this->userHasAnyAdminRole($userId)) {
            return response()->json([
                'message' => "Impossible d'ajouter un admin comme joueur (étudiants uniquement).",
            ], 422);
        }

        $isTeamMode = ($activity->mode ?? 'INDIVIDUAL') === 'TEAM';

        // Validate team_id only in TEAM mode
        if (!$isTeamMode) {
            $teamId = null; // ignore silently in INDIVIDUAL mode
        } elseif ($teamId !== null) {
            $teamExists = DB::table('activity_teams')
                ->where('activity_id', (int) $activity->id)
                ->where('id', $teamId)
                ->exists();

            if (!$teamExists) {
                return response()->json(['message' => "Équipe invalide."], 422);
            }
        }

        $inserted = false;

        DB::transaction(function () use ($activity, $actorId, $userId, $teamId, $isTeamMode, &$inserted) {
            // Ensure participant exists
            $participantRow = [
                'activity_id' => (int) $activity->id,
                'user_id' => $userId,
                'created_by_id' => $actorId,
            ];
            $this->addTimestampsIfColumnsExist('activity_participants', $participantRow);

            $inserted = (bool) DB::table('activity_participants')->insertOrIgnore($participantRow);

            if (!$isTeamMode) return;

            // Remove previous team membership for THIS activity (one team max)
            DB::table('activity_team_members as atm')
                ->join('activity_teams as t', 't.id', '=', 'atm.team_id')
                ->where('t.activity_id', (int) $activity->id)
                ->where('atm.user_id', $userId)
                ->delete();

            // Assign if provided
            if ($teamId !== null) {
                $memberRow = [
                    'team_id' => $teamId,
                    'user_id' => $userId,
                    'created_by_id' => $actorId,
                ];
                $this->addTimestampsIfColumnsExist('activity_team_members', $memberRow);

                DB::table('activity_team_members')->insertOrIgnore($memberRow);
            }
        });

        return response()->json([
            'data' => [
                'activity_id' => (int) $activity->id,
                'user_id' => $userId,
                'team_id' => $teamId,
                'inserted' => $inserted,
            ],
        ], $inserted ? 201 : 200);
    }

    public function destroy(Request $request, Activity $activity, int $userId)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $deleted = DB::table('activity_participants')
            ->where('activity_id', (int) $activity->id)
            ->where('user_id', (int) $userId)
            ->delete();

        // Also detach from team (this activity)
        DB::table('activity_team_members as atm')
            ->join('activity_teams as t', 't.id', '=', 'atm.team_id')
            ->where('t.activity_id', (int) $activity->id)
            ->where('atm.user_id', (int) $userId)
            ->delete();

        return response()->json([
            'data' => [
                'deleted' => (bool) $deleted,
            ],
        ]);
    }

    /**
     * Set the TARGET total points for a participant (writes a delta transaction).
     * Only SUPER_ADMIN or GAMEMASTER of this activity.
     */
    public function setPoints(Request $request, Activity $activity, int $userId)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        // Must be able to manage the activity (creator, activity_admins, or super admin)
        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) ($activity->created_by_id ?? 0))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Must be SUPER_ADMIN or GAMEMASTER
        $isSuper = $this->userHasRole($actorId, 'ROLE_SUPER_ADMIN');
        $isGm = $this->userHasRole($actorId, 'ROLE_GAMEMASTER');
        if (!$isSuper && !$isGm) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'target_points' => ['required', 'integer'],
        ]);

        $target = (int) $validated['target_points'];

        $isParticipant = DB::table('activity_participants')
            ->where('activity_id', (int) $activity->id)
            ->where('user_id', (int) $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['message' => "Joueur non inscrit à l’activité."], 422);
        }

        $current = (int) DB::table('point_transactions')
            ->where('activity_id', (int) $activity->id)
            ->where('user_id', (int) $userId)
            ->sum('amount');

        $delta = $target - $current;

        $inserted = false;

        if ($delta !== 0) {
            DB::table('point_transactions')->insert([
                'user_id' => (int) $userId,
                'amount' => (int) $delta,
                'reason' => 'Ajustement admin activité ' . $activity->title,
                'context_type' => 'activity',
                'context_id' => (int) $activity->id,
                'activity_id' => (int) $activity->id,
                'created_by_id' => (int) $actorId,
                'created_at' => now(),
            ]);
            $inserted = true;
        }

        return response()->json([
            'data' => [
                'activity_id' => (int) $activity->id,
                'user_id' => (int) $userId,
                'previous_points' => $current,
                'target_points' => $target,
                'delta' => $delta,
                'inserted' => $inserted,
            ],
        ]);
    }


    private function addTimestampsIfColumnsExist(string $table, array &$row): void
    {
        $now = now();

        if (Schema::hasColumn($table, 'created_at') && !array_key_exists('created_at', $row)) {
            $row['created_at'] = $now;
        }
        if (Schema::hasColumn($table, 'updated_at') && !array_key_exists('updated_at', $row)) {
            $row['updated_at'] = $now;
        }
    }

    private function canManageActivity(int $actorId, int $activityId, int $createdById): bool
    {
        if ($this->userHasRole($actorId, 'ROLE_SUPER_ADMIN')) return true;
        if ($createdById > 0 && $actorId === $createdById) return true;

        return DB::table('activity_admins')
            ->where('activity_id', $activityId)
            ->where('user_id', $actorId)
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

    private function userHasAnyAdminRole(int $userId): bool
    {
        return DB::table('user_roles')
            ->where('user_id', $userId)
            ->exists();
    }
}

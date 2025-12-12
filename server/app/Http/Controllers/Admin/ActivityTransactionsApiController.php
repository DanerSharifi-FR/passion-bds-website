<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityTransactionsApiController extends Controller
{
    public function index(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) $activity->created_by_id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = min(max($perPage, 1), 100);

        $q = trim((string) $request->query('q', ''));
        $userId = (int) $request->query('user_id', 0);

        $base = DB::table('point_transactions as pt')
            ->join('users as u', 'u.id', '=', 'pt.user_id')
            ->leftJoin('users as a', 'a.id', '=', 'pt.created_by_id')
            ->where('pt.activity_id', (int) $activity->id);

        if ($userId > 0) {
            $base->where('pt.user_id', $userId);
        }

        if ($q !== '') {
            $base->where(function ($sub) use ($q) {
                $sub->where('u.display_name', 'like', '%' . $q . '%')
                    ->orWhere('u.university_email', 'like', '%' . $q . '%')
                    ->orWhere('pt.reason', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderByDesc('pt.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->select([
                'pt.id',
                'pt.amount',
                'pt.reason',
                'pt.context_type',
                'pt.context_id',
                'pt.created_at',

                'u.id as user_id',
                'u.display_name as user_name',
                'u.university_email as user_email',

                'a.display_name as admin_name',
            ])
            ->get();

        $lastPage = (int) max(1, (int) ceil($total / $perPage));

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    public function storeManual(Request $request, Activity $activity)
    {
        $actor = $request->user();
        if (!$actor) return response()->json(['message' => 'Unauthorized'], 401);

        $actorId = (int) $actor->id;

        if (!$this->canManageActivity($actorId, (int) $activity->id, (int) $activity->created_by_id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $userId = (int) $validated['user_id'];
        $amount = (int) $validated['amount'];
        $reason = trim((string) $validated['reason']);

        $isParticipant = DB::table('activity_participants')
            ->where('activity_id', (int) $activity->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json([
                'message' => "Cet utilisateur n'est pas un joueur de cette activité.",
            ], 422);
        }

        $id = DB::table('point_transactions')->insertGetId([
            'user_id' => $userId,
            'amount' => $amount,
            'reason' => $reason,
            'context_type' => 'MANUAL',
            'context_id' => null,
            'created_by_id' => $actorId,
            'activity_id' => (int) $activity->id,
            'created_at' => now(),
        ]);

        $totalPoints = (int) DB::table('point_transactions')
            ->where('activity_id', (int) $activity->id)
            ->where('user_id', $userId)
            ->sum('amount');

        return response()->json([
            'data' => [
                'transaction_id' => (int) $id,
                'activity_id' => (int) $activity->id,
                'user_id' => $userId,
                'amount' => $amount,
                'reason' => $reason,
                'new_total' => $totalPoints,
            ],
        ], 201);
    }

    private function canManageActivity(int $actorId, int $activityId, int $ownerId): bool
    {
        if ($this->userHasRole($actorId, 'ROLE_SUPER_ADMIN')) return true;
        if ($actorId === $ownerId) return true;

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

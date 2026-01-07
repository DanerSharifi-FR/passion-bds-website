<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLeaderboardApiController extends Controller
{
    public function index(Request $request, Activity $activity)
    {
        $limit = max(1, min(500, (int) $request->query('limit', 200)));
        $mode = (string) (($activity->mode ?? 'INDIVIDUAL') === 'TEAM' ? 'TEAM' : 'INDIVIDUAL');

        return $mode === 'TEAM'
            ? $this->teamLeaderboard($request, $activity, $limit)
            : $this->individualLeaderboard($request, $activity, $limit);
    }

    private function individualLeaderboard(Request $request, Activity $activity, int $limit)
    {
        $currentUserId = (int) ($request->user()?->id ?? 0);

        $pointsSub = DB::table('point_transactions')
            ->select('user_id', DB::raw('SUM(amount) as points_total'))
            ->where('activity_id', (int) $activity->id)
            ->groupBy('user_id');

        $rows = DB::table('activity_participants as ap')
            ->join('users as u', 'u.id', '=', 'ap.user_id')
            ->leftJoinSub($pointsSub, 'pts', function ($join) {
                $join->on('pts.user_id', '=', 'u.id');
            })
            ->where('ap.activity_id', (int) $activity->id)
            ->orderByDesc(DB::raw('COALESCE(pts.points_total, 0)'))
            ->orderByRaw("COALESCE(u.display_name, '') ASC")
            ->limit($limit)
            ->get([
                'u.id as id',
                DB::raw("COALESCE(NULLIF(u.display_name,''), u.university_email) as name"),
                'u.university_email as email',
                DB::raw('COALESCE(pts.points_total, 0) as points'),
            ])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'email' => (string) $r->email,
                'points' => (int) $r->points,
                'isUser' => $currentUserId > 0 && (int) $r->id === $currentUserId,
            ])
            ->values();

        $ranked = $this->applyDenseRank($rows, fn ($x) => (int) $x['points']);

        return response()->json([
            'data' => $ranked,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'activity_id' => (int) $activity->id,
                'mode' => 'INDIVIDUAL',
                'limit' => $limit,
            ],
        ]);
    }

    private function teamLeaderboard(Request $request, Activity $activity, int $limit)
    {
        $currentUserId = (int) ($request->user()?->id ?? 0);

        $myTeamId = null;
        if ($currentUserId > 0) {
            $myTeamId = DB::table('activity_team_members as atm')
                ->join('activity_teams as t', 't.id', '=', 'atm.team_id')
                ->where('t.activity_id', (int) $activity->id)
                ->where('atm.user_id', $currentUserId)
                ->value('t.id');
            $myTeamId = $myTeamId !== null ? (int) $myTeamId : null;
        }

        $rows = DB::table('activity_teams as t')
            ->leftJoin('activity_team_members as m', 'm.team_id', '=', 't.id')
            ->leftJoin('point_transactions as pt', function ($join) {
                $join->on('pt.user_id', '=', 'm.user_id')
                    ->on('pt.activity_id', '=', 't.activity_id');
            })
            ->where('t.activity_id', (int) $activity->id)
            ->groupBy('t.id', 't.title', 't.activity_id')
            ->orderByDesc(DB::raw('COALESCE(SUM(pt.amount), 0)'))
            ->orderByRaw("COALESCE(t.title,'') ASC")
            ->limit($limit)
            ->get([
                't.id',
                't.title',
                DB::raw('COUNT(DISTINCT m.user_id) as members_count'),
                DB::raw('COALESCE(SUM(pt.amount), 0) as points'),
            ])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'title' => (string) $r->title,
                'members_count' => (int) $r->members_count,
                'points' => (int) $r->points,
                'isUserTeam' => $myTeamId !== null && (int) $r->id === $myTeamId,
            ])
            ->values();

        $ranked = $this->applyDenseRank($rows, fn ($x) => (int) $x['points']);

        return response()->json([
            'data' => $ranked,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'activity_id' => (int) $activity->id,
                'mode' => 'TEAM',
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Dense rank: same points => same rank, next rank increments by 1.
     */
    private function applyDenseRank($rows, callable $scoreFn)
    {
        $rank = 0;
        $prevScore = null;

        return $rows->map(function ($row) use ($scoreFn, &$rank, &$prevScore) {
            $score = $scoreFn($row);
            if ($prevScore === null || $score !== $prevScore) {
                $rank++;
                $prevScore = $score;
            }

            $row['rank'] = $rank;
            return $row;
        })->values();
    }
}

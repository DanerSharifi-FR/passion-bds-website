<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function home(): Factory|View
    {
        $leaderboardWidget = $this->buildLeaderboardWidgetData(auth()->id());

        return view('home', [
            'leaderboardWidget' => $leaderboardWidget,
        ]);
    }

    public function team(): Factory|View
    {
        return view('team');
    }

    public function gallery(): Factory|View
    {
        return view('gallery');
    }

    public function leaderboard(): Factory|View
    {
        return view('leaderboard');
    }

    public function login(): Factory|View
    {
        return view('login');
    }

    private function buildLeaderboardWidgetData(?int $currentUserId): array
    {
        // 1) points per user (no roles logic here)
        $pointsPerUserQuery = DB::table('users')
            ->leftJoin('point_transactions as pt', 'pt.user_id', '=', 'users.id')
            ->selectRaw('users.id')
            ->selectRaw('COALESCE(NULLIF(users.display_name, ""), users.university_email) as display_label')
            ->selectRaw('users.university_email as email')
            ->selectRaw('COALESCE(SUM(pt.amount), 0) as points')
            ->groupBy('users.id', 'users.display_name', 'users.university_email');

        // 2) rank + "user above" using window functions (MySQL 8+)
        $rankedQuery = DB::query()
            ->fromSub($pointsPerUserQuery, 't')
            ->selectRaw('t.*')
            ->selectRaw('DENSE_RANK() OVER (ORDER BY t.points DESC) as user_rank')
            ->selectRaw('LAG(t.points) OVER (ORDER BY t.points DESC) as points_above')
            ->selectRaw('LAG(t.display_label) OVER (ORDER BY t.points DESC) as label_above');

        $podium = (clone $rankedQuery)
            ->limit(3)
            ->get()
            ->map(fn ($row) => [
                'rank' => (int) $row->user_rank,
                'name' => $row->display_label,
                'email' => $row->email,
                'points' => (int) $row->points,
            ])
            ->values()
            ->all();

        $me = null;

        if ($currentUserId) {
            $meRow = DB::query()
                ->fromSub($rankedQuery, 'r')
                ->where('r.id', $currentUserId)
                ->first();

            if ($meRow) {
                $myPoints = (int) $meRow->points;
                $pointsAbove = $meRow->points_above !== null ? (int) $meRow->points_above : null;
                $gapToAbove = $pointsAbove !== null ? max(0, $pointsAbove - $myPoints) : 0;

                $me = [
                    'rank' => (int) $meRow->user_rank,
                    'name' => $meRow->display_label,
                    'email' => $meRow->email,
                    'points' => $myPoints,

                    // for the “X pts de retard sur …” line
                    'above_name' => $meRow->label_above,
                    'gap_to_above' => $gapToAbove,
                    'points_above' => $pointsAbove,

                    // optional: progress bar % towards the user above
                    'progress_to_above_pct' => $pointsAbove ? (int) min(100, round(($myPoints / $pointsAbove) * 100)) : 100,
                ];
            }
        }

        return [
            'podium' => $podium, // top 3
            'me' => $me,         // null if guest
        ];
    }
}

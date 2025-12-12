<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Random\RandomException;

class PageController extends Controller
{
    public function home(): Factory|View
    {
        $leaderboardWidget = $this->buildLeaderboardWidgetData(auth()->id());

        return view('home', [
            'leaderboardWidget' => $leaderboardWidget,
        ]);
    }

    /**
     * @throws RandomException
     */
    public function team(): Factory|View
    {
        // ✅ “Random per session” but reshuffled every week
        // Week key ISO (year-week) so it changes automatically each week.
        $weekKey = now()->format('o-\WW');

        if (session('team_visuals_week') !== $weekKey) {
            session([
                'team_visuals_week' => $weekKey,
                'team_visuals_seed' => random_int(1, PHP_INT_MAX),
            ]);
        }

        $baseSeed = (int) session('team_visuals_seed', 123456);

        $visiblePoles = DB::table('poles')
            ->where('is_visible', 1)
            ->orderBy('position')
            ->get(['id', 'name', 'slug', 'description', 'icon_name', 'position']);

        $visibleMembers = DB::table('team_members')
            ->where('is_visible', 1)
            ->orderBy('pole_id')
            ->orderBy('position')
            ->get([
                'id',
                'pole_id',
                'full_name',
                'nickname',
                'bio',
                'photo_url',
                'instagram_url',
                'position',
            ]);

        $membersGroupedByPoleId = $visibleMembers->groupBy('pole_id');

        $teamPoles = $visiblePoles->map(function ($pole) use ($membersGroupedByPoleId, $baseSeed) {
            $members = ($membersGroupedByPoleId->get($pole->id, collect()))
                ->values()
                ->map(function ($member) use ($baseSeed) {
                    // ✅ Deterministic per member, per week, per session
                    // (no global mt_srand, no side effects)
                    $localSeed = (int) sprintf('%u', crc32($baseSeed . '|' . $member->id));
                    $rng = new \Random\Randomizer(new \Random\Engine\Mt19937($localSeed));

                    // Rarity roll (tweak thresholds if you want)
                    $rarityRoll = $rng->getInt(1, 100);
                    $rarityClass = match (true) {
                        $rarityRoll <= 45 => 'rarity-common',
                        $rarityRoll <= 70 => 'rarity-rare',
                        $rarityRoll <= 85 => 'rarity-epic',
                        $rarityRoll <= 95 => 'rarity-legendary',
                        default => 'rarity-champion',
                    };

                    // Elixir roll (mostly 1-10, tiny chance of special)
                    $specialRoll = $rng->getInt(1, 250);
                    if ($specialRoll === 1) {
                        $elixirValue = '∞';
                    } elseif ($specialRoll === 2) {
                        $elixirValue = '99';
                    } else {
                        $elixirValue = (string) $rng->getInt(1, 10);
                    }

                    return [
                        'id' => (int) $member->id,
                        'pole_id' => (int) $member->pole_id,
                        'full_name' => $member->full_name,
                        'nickname' => $member->nickname,
                        'bio' => $member->bio,
                        'photo_url' => asset(ltrim($member->photo_url, '/')),
                        'instagram_url' => $member->instagram_url,
                        'position' => (int) $member->position,

                        // ✅ New: random visuals
                        'rarity_class' => $rarityClass,
                        'elixir_value' => $elixirValue,
                    ];
                })
                ->all();

            return [
                'id' => (int) $pole->id,
                'name' => $pole->name,
                'slug' => $pole->slug,
                'position' => (int) $pole->position,
                'members' => $members,
            ];
        })->all();

        return view('team', [
            'teamPoles' => $teamPoles,
        ]);
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

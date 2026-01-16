<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Allo;
use App\Models\AlloPageView;
use App\Models\AlloSlot;
use App\Models\AlloUsage;
use App\Models\User;
use App\Services\AlloUsageService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Random\RandomException;

class PageController extends Controller
{
    public function home(): Factory|View
    {
        $leaderboardWidget = $this->buildLeaderboardWidgetData(auth()->id());

        $randomAllos = Allo::query()
            ->where('status', 'OPEN')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        return view('home', [
            'leaderboardWidget' => $leaderboardWidget,
            'randomAllos' => $randomAllos,
        ]);
    }

    public function allos()
    {
        return view('allos');
    }

    public function alloReservations(): Factory|View
    {
        return view('allo-reservations');
    }

    public function alloSlots(Request $request, int $alloId): Factory|View|RedirectResponse
    {
        /** @var Allo $allo */
        $allo = Allo::query()
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->findOrFail($alloId);

        $now = now();
        $user = $request->user();
        $canBookNew = $user instanceof User ? $this->canBookNew($allo, $user, $now) : true;

        $existingBooking = null;

        if ($user !== null) {
            $existingBooking = AlloUsage::query()
                ->where('user_id', $user->id)
                ->where('allo_id', $allo->id)
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->orderByDesc('slot_start_at')
                ->first();

            if ($existingBooking !== null && ! $canBookNew) {
                if ($existingBooking->status !== AlloUsageService::STATUS_PENDING) {
                    return redirect()
                        ->route('allos.reservations')
                        ->with('toast', [
                            'type' => 'error',
                            'message' => 'Tu as atteint ta limite de réservations pour cet allo.',
                        ]);
                }
            }
        }

        if ($user !== null && ! $canBookNew && $existingBooking === null) {
            abort(404);
        }

        $windowBounds = $this->resolveWindowBounds($allo);
        $windowEnd = $windowBounds[1] ?? null;
        $availabilityThreshold = $now->copy()->addMinutes(max((int) ($allo->security_margin_minutes ?? 0), 0));

        $hasAnySlots = AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->exists();
        $hasFutureSlots = AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->where('slot_start_at', '>=', $availabilityThreshold)
            ->exists();

        $slotsPassed = $hasAnySlots && ! $hasFutureSlots;
        $isEnded = $allo->status !== 'OPEN'
            || ($windowEnd !== null && $now->greaterThan($windowEnd))
            || $slotsPassed;

        abort_if($isEnded, 404);

        if ($user !== null) {
            try {
                $startOfToday = now()->startOfDay();
                $startOfTomorrow = $startOfToday->copy()->addDay();

                $alreadyLoggedToday = AlloPageView::query()
                    ->where('allo_id', $allo->id)
                    ->where('user_id', $user->id)
                    ->where('viewed_at', '>=', $startOfToday)
                    ->where('viewed_at', '<', $startOfTomorrow)
                    ->exists();

                if (! $alreadyLoggedToday) {
                    // Journalise 1 visite / jour (jour calendaire).
                    AlloPageView::create([
                        'allo_id' => $allo->id,
                        'user_id' => $user->id,
                        'viewed_at' => now(),
                    ]);
                }
            } catch (\Throwable $exception) {
                logger()->warning('Impossible de tracer la visite allo.', [
                    'allo_id' => $allo->id,
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }


        return view('allo-slots', [
            'alloId' => $alloId,
        ]);
    }

    private function canBookNew(Allo $allo, User $user, Carbon $now): bool
    {
        $dailyLimit = $allo->daily_booking_limit;

        if ($dailyLimit === null || $dailyLimit <= 0) {
            return true;
        }

        $availabilityThreshold = $now->copy()->addMinutes(max((int) ($allo->security_margin_minutes ?? 0), 0));
        $slotCapacityFallback = $allo->resolveSlotCapacity();

        $bookingCountsByDate = AlloUsage::query()
            ->selectRaw('DATE(slot_start_at) as slot_date, COUNT(*) as bookings_count')
            ->where('user_id', $user->id)
            ->where('allo_id', $allo->id)
            ->whereIn('status', [
                AlloUsageService::STATUS_PENDING,
                AlloUsageService::STATUS_ACCEPTED,
                AlloUsageService::STATUS_DONE,
            ])
            ->groupBy('slot_date')
            ->get()
            ->pluck('bookings_count', 'slot_date');

        $slots = AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->where('slot_start_at', '>=', $availabilityThreshold)
            ->whereIn('status', ['available', 'partial'])
            ->withCount(['usages as bookings_count' => function ($usageQuery): void {
                $usageQuery->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ]);
            }])
            ->get();

        return $slots->contains(function (AlloSlot $slot) use ($bookingCountsByDate, $dailyLimit, $slotCapacityFallback): bool {
            $slotDate = $slot->slot_start_at?->toDateString();

            if ($slotDate === null) {
                return false;
            }

            $capacity = (int) ($slot->capacity ?? $slotCapacityFallback);
            $bookingsCount = (int) ($slot->bookings_count ?? 0);
            $remaining = max($capacity - $bookingsCount, 0);

            if ($remaining <= 0) {
                return false;
            }

            $currentCount = (int) ($bookingCountsByDate[$slotDate] ?? 0);

            return $currentCount < $dailyLimit;
        });
    }

    public function activities(): Factory|View
    {
        return view('activities');
    }

    public function activityLeaderboard(String $activity_slug): Factory|View
    {
        $activity = Activity::where('slug', $activity_slug)->firstOrFail();

        abort_unless((bool) $activity->is_active, 404);

        return view('activity_leaderboard', [
            'activity' => $activity,
        ]);
    }

    /**
     * @throws RandomException
     */
    public function team(): Factory|View
    {
        // Random per session, reshuffles every week
        $weekKey = now()->format('o-\WW');

        if (session('team_visuals_week_v2') !== $weekKey) {
            session([
                'team_visuals_week_v2' => $weekKey,
                'team_visuals_seed_v2' => random_int(1, PHP_INT_MAX),
            ]);
        }

        $baseSeed = (int) session('team_visuals_seed_v2', 123456);

        $rarityClasses = [
            'rarity-common',
            'rarity-rare',
            'rarity-epic',
            'rarity-legendary',
            'rarity-champion',
        ];

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

        // 1) Deterministic random per member
        $membersArray = $visibleMembers
            ->values()
            ->map(function ($member) use ($baseSeed) {
                $localSeed = (int) sprintf('%u', crc32($baseSeed . '|' . $member->id));
                $rng = new \Random\Randomizer(new \Random\Engine\Mt19937($localSeed));

                $rarityRoll = $rng->getInt(1, 100);
                $rarityClass = match (true) {
                    $rarityRoll <= 45 => 'rarity-common',
                    $rarityRoll <= 70 => 'rarity-rare',
                    $rarityRoll <= 85 => 'rarity-epic',
                    $rarityRoll <= 95 => 'rarity-legendary',
                    default => 'rarity-champion',
                };

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

                    'rarity_class' => $rarityClass,
                    'elixir_value' => $elixirValue,
                ];
            })
            ->all();

        // 2) Enforce: at least one of each rarity (if enough members)
        if (count($membersArray) >= count($rarityClasses)) {
            $globalRng = new \Random\Randomizer(new \Random\Engine\Mt19937($baseSeed));

            $counts = array_fill_keys($rarityClasses, 0);
            foreach ($membersArray as $m) {
                $counts[$m['rarity_class']] = ($counts[$m['rarity_class']] ?? 0) + 1;
            }

            $missing = array_values(array_filter(
                $rarityClasses,
                fn ($c) => ($counts[$c] ?? 0) === 0
            ));

            $usedIndexes = [];

            foreach ($missing as $missingClass) {
                $n = count($membersArray);
                $pickedIndex = null;

                // pick someone from a class that has > 1 (avoid creating a new missing)
                for ($attempts = 0; $attempts < 300; $attempts++) {
                    $idx = $globalRng->getInt(0, $n - 1);
                    if (isset($usedIndexes[$idx])) {
                        continue;
                    }

                    $currentClass = $membersArray[$idx]['rarity_class'];
                    if (($counts[$currentClass] ?? 0) <= 1) {
                        continue;
                    }

                    $pickedIndex = $idx;
                    break;
                }

                // fallback: any non-used index
                if ($pickedIndex === null) {
                    for ($idx = 0; $idx < $n; $idx++) {
                        if (!isset($usedIndexes[$idx])) {
                            $pickedIndex = $idx;
                            break;
                        }
                    }
                }

                if ($pickedIndex !== null) {
                    $oldClass = $membersArray[$pickedIndex]['rarity_class'];
                    $membersArray[$pickedIndex]['rarity_class'] = $missingClass;

                    $counts[$oldClass]--;
                    $counts[$missingClass]++;

                    $usedIndexes[$pickedIndex] = true;
                }
            }
        }

        // 3) Group by pole and build view structure
        $membersGroupedByPoleId = collect($membersArray)->groupBy('pole_id');

        $teamPoles = $visiblePoles->map(function ($pole) use ($membersGroupedByPoleId) {
            return [
                'id' => (int) $pole->id,
                'name' => $pole->name,
                'slug' => $pole->slug,
                'position' => (int) $pole->position,
                'members' => $membersGroupedByPoleId->get((int) $pole->id, collect())->values()->all(),
            ];
        })->all();

        return view('team', [
            'teamPoles' => $teamPoles,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function resolveWindowBounds(Allo $allo): ?array
    {
        if ($allo->window_start_at !== null && $allo->window_end_at !== null) {
            return [$allo->window_start_at, $allo->window_end_at];
        }

        $timeSlots = $allo->time_slots;

        if (!is_array($timeSlots) || count($timeSlots) === 0) {
            return null;
        }

        $minStart = null;
        $maxEnd = null;

        foreach ($timeSlots as $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $startDate = $slot['start_date'] ?? null;
            $endDate = $slot['end_date'] ?? null;
            $startTime = $slot['start_time'] ?? null;
            $endTime = $slot['end_time'] ?? null;

            if (!is_string($startDate) || !is_string($endDate) || !is_string($startTime) || !is_string($endTime)) {
                continue;
            }

            $start = Carbon::createFromFormat('Y-m-d H:i', "{$startDate} {$startTime}");
            $end = Carbon::createFromFormat('Y-m-d H:i', "{$endDate} {$endTime}");

            if (! $start instanceof Carbon || ! $end instanceof Carbon) {
                continue;
            }

            $minStart = $minStart === null || $start->lessThan($minStart) ? $start : $minStart;
            $maxEnd = $maxEnd === null || $end->greaterThan($maxEnd) ? $end : $maxEnd;
        }

        if ($minStart === null || $maxEnd === null) {
            return null;
        }

        return [$minStart, $maxEnd];
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

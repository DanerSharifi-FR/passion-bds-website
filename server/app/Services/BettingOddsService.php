<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BettingOddsService
{
    /**
     * Recalcule les cotes pour les options d'un match.
     *
     * @param int $matchId
     * @return array<int, float> [optionId => cote]
     */
    public function recomputeForMatch(int $matchId): array
    {
        $seed = (float) config('betting.odds_seed');
        $minOdds = (float) config('betting.min_odds');
        $maxOdds = (float) config('betting.max_odds');
        $seedPerOption = $seed / 3.0;

        $options = DB::table('bet_options')
            ->where('match_id', $matchId)
            ->orderBy('id')
            ->get(['id', 'pool_total']);

        if ($options->isEmpty()) {
            return [];
        }

        $totalPool = (float) $options->sum('pool_total');
        $denomTotal = $totalPool + $seed;
        $now = now();

        $result = [];

        foreach ($options as $option) {
            $denom = (float) $option->pool_total + $seedPerOption;
            $raw = $denomTotal / $denom;
            $clamped = min($maxOdds, max($minOdds, $raw));
            $rounded = round($clamped, 2);

            DB::table('bet_options')
                ->where('id', $option->id)
                ->update([
                    'current_odds' => $rounded,
                    'updated_at' => $now,
                ]);

            $result[(int) $option->id] = $rounded;
        }

        return $result;
    }
}

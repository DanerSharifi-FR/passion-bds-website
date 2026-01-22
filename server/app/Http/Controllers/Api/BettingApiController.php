<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BetMatch;
use Illuminate\Http\JsonResponse;

class BettingApiController extends Controller
{
    public function index(): JsonResponse
    {
        $matches = BetMatch::query()
            ->with('options')
            ->where('is_visible', true)
            ->orderBy('match_start_at')
            ->get();

        $data = $matches->map(function (BetMatch $match): array {
            return [
                'id' => $match->id,
                'title' => $match->title,
                'status' => $match->status->value,
                'bet_open_at' => optional($match->bet_open_at)->toISOString(),
                'match_start_at' => optional($match->match_start_at)->toISOString(),
                'match_end_at' => optional($match->match_end_at)->toISOString(),
                'options' => $match->options->map(static function ($option): array {
                    return [
                        'id' => (int) $option->id,
                        'label' => $option->label,
                        'current_odds' => (float) $option->current_odds,
                        'pool_total' => (int) $option->pool_total,
                        'created_at' => optional($option->created_at)->toISOString(),
                        'updated_at' => optional($option->updated_at)->toISOString(),
                    ];
                })->values(),
                'created_at' => optional($match->created_at)->toISOString(),
                'updated_at' => optional($match->updated_at)->toISOString(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    public function show(BetMatch $match): JsonResponse
    {
        if (!$match->isUserVisible()) {
            abort(404);
        }

        $match->load('options');

        return response()->json([
            'data' => [
                'id' => $match->id,
                'title' => $match->title,
                'status' => $match->status->value,
                'bet_open_at' => optional($match->bet_open_at)->toISOString(),
                'match_start_at' => optional($match->match_start_at)->toISOString(),
                'match_end_at' => optional($match->match_end_at)->toISOString(),
                'options' => $match->options->map(static function ($option): array {
                    return [
                        'id' => (int) $option->id,
                        'label' => $option->label,
                        'current_odds' => (float) $option->current_odds,
                        'pool_total' => (int) $option->pool_total,
                        'created_at' => optional($option->created_at)->toISOString(),
                        'updated_at' => optional($option->updated_at)->toISOString(),
                    ];
                })->values(),
                'created_at' => optional($match->created_at)->toISOString(),
                'updated_at' => optional($match->updated_at)->toISOString(),
            ],
        ]);
    }
}

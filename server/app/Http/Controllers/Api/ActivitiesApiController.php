<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivitiesApiController extends Controller
{
    public function index(Request $request)
    {
        $limit = max(1, min(200, (int) $request->query('limit', 200)));

        $activities = DB::table('activities as a')
            ->where('a.is_active', 1)
            ->orderBy('a.created_at', 'asc')
            ->limit($limit)
            ->get([
                'a.id',
                'a.title',
                'a.mode',
                'a.points_label',
                'a.is_active',
            ])
            ->map(function ($a) {
                return [
                    'id' => (int) $a->id,
                    'title' => (string) $a->title,
                    'mode' => (string) ($a->mode ?? 'INDIVIDUAL'),
                    'points_label' => (string) ($a->points_label ?? 'Points'),
                    'is_active' => (bool) $a->is_active,
                ];
            })
            ->values();

        return response()->json([
            'data' => $activities,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'limit' => $limit,
                'count' => $activities->count(),
            ],
        ]);
    }
}

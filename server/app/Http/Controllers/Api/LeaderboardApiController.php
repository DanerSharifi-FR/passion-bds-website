<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 100);
        $limit = max(1, min($limit, 300));

        $currentUserId = auth()->id();

        // points per user (NO roles)
        $pointsPerUser = DB::table('users')
            ->leftJoin('point_transactions as pt', 'pt.user_id', '=', 'users.id')
            ->selectRaw('users.id')
            ->selectRaw('COALESCE(NULLIF(users.display_name, ""), users.university_email) as name')
            ->selectRaw('users.university_email as email')
            ->selectRaw('COALESCE(SUM(pt.amount), 0) as points')
            ->groupBy('users.id', 'users.display_name', 'users.university_email');

        // rank with window functions (MySQL 8+)
        $ranked = DB::query()
            ->fromSub($pointsPerUser, 't')
            ->selectRaw('t.*')
            ->selectRaw('DENSE_RANK() OVER (ORDER BY t.points DESC) as user_rank')
            ->orderByDesc('t.points')
            ->orderBy('t.id'); // stable ordering

        $rows = $ranked->limit($limit)->get();

        $data = $rows->map(function ($row) use ($currentUserId) {
            $emojiPool = ['😎', '🧢', '🌸', '🍕', '👀', '💤', '🎸', '🎨', '🚲', '🧸', '🎈', '📷', '💡', '⚡'];
            $seed = crc32((string) $row->email);
            $avatar = $emojiPool[$seed % count($emojiPool)];

            return [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'points' => (int) $row->points,
                'rank' => (int) $row->user_rank,
                'avatar' => $avatar,
                'isUser' => $currentUserId && (int)$row->id === (int)$currentUserId,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toISOString(),
                'limit' => $limit,
            ],
        ]);
    }
}

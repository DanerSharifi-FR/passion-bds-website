<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogsApiController extends Controller
{
    // No pagination UI. Still keep a sane cap for the initial snapshot.
    private const SNAPSHOT_MAX = 200;

    public function index(Request $request)
    {
        $afterId = max(0, (int) $request->query('after_id', 0));

        $query = AuditLog::query()
            ->leftJoin('users', 'users.id', '=', 'audit_logs.actor_id')
            ->select([
                'audit_logs.id',
                'audit_logs.actor_id',
                'audit_logs.action',
                'audit_logs.entity_type',
                'audit_logs.entity_id',
                'audit_logs.ip_address',
                'audit_logs.user_agent',
                'audit_logs.created_at',
                'users.display_name as actor_display_name',
                'users.university_email as actor_university_email',
            ]);

        if ($afterId > 0) {
            $query->where('audit_logs.id', '>', $afterId)
                ->orderBy('audit_logs.id', 'asc');
        } else {
            $query->orderBy('audit_logs.id', 'desc');
        }

        $rows = $query->limit(200)->get();

        $data = $rows->map(static function ($row) {
            return [
                'id' => (int) $row->id,
                'actor_id' => $row->actor_id ? (int) $row->actor_id : null,
                'actor_name' => $row->actor_display_name ?: ($row->actor_university_email ?: 'Anonyme'),
                'action' => $row->action,
                'entity_type' => $row->entity_type,
                'entity_id' => $row->entity_id !== null ? (int) $row->entity_id : null,
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'created_at' => optional($row->created_at)->toISOString(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }


    public function stream(Request $request): StreamedResponse
    {
        $lastEventId = (int) ($request->header('Last-Event-ID') ?: $request->query('after_id', 0));
        $lastEventId = max(0, $lastEventId);

        return response()->stream(function () use ($lastEventId) {
            // Important for SSE
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) { @ob_end_flush(); }
            ob_implicit_flush(true);

            set_time_limit(0);

            $currentLastId = $lastEventId;
            $lastPingAt = time();

            while (!connection_aborted()) {
                $rows = AuditLog::query()
                    ->leftJoin('users', 'users.id', '=', 'audit_logs.actor_id')
                    ->select([
                        'audit_logs.id',
                        'audit_logs.actor_id',
                        'audit_logs.action',
                        'audit_logs.entity_type',
                        'audit_logs.entity_id',
                        'audit_logs.created_at',
                        'users.display_name as actor_display_name',
                        'users.university_email as actor_university_email',
                    ])
                    ->where('audit_logs.id', '>', $currentLastId)
                    ->orderBy('audit_logs.id', 'asc')
                    ->limit(100)
                    ->get();

                foreach ($rows as $row) {
                    $payload = [
                        'id' => (int) $row->id,
                        'actor_id' => $row->actor_id ? (int) $row->actor_id : null,
                        'actor_name' => $row->actor_display_name ?: ($row->actor_university_email ?: 'Anonyme'),
                        'actor' => [
                            'display_name' => $row->actor_display_name,
                            'university_email' => $row->actor_university_email,
                        ],
                        'action' => $row->action,
                        'entity_type' => $row->entity_type,
                        'entity_id' => $row->entity_id !== null ? (int) $row->entity_id : null,
                        'created_at' => optional($row->created_at)->toISOString(),
                    ];

                    echo "id: {$payload['id']}\n";
                    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";

                    $currentLastId = (int) $row->id;
                }

                // keep-alive ping every ~15s (prevents proxies killing the connection)
                if (time() - $lastPingAt >= 15) {
                    echo ": ping\n\n";
                    $lastPingAt = time();
                }

                // Small sleep to avoid burning CPU
                usleep(500000); // 0.5s
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // nginx: disable buffering
        ]);
    }
}

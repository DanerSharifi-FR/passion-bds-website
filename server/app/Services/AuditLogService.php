<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function log(
        ?User $actor,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = [],
        ?string $ip = null,
        ?string $userAgent = null,
    ): void {
        AuditLog::create([
            'actor_id' => $actor?->id,

            'action' => mb_substr(trim($action), 0, 100),
            'entity_type' => mb_substr(trim($entityType), 0, 100),
            'entity_id' => $entityId,

            'description' => $description !== null ? mb_substr(trim($description), 0, 255) : null,
            'metadata_json' => $metadata !== [] ? $metadata : null,

            'ip_address' => $ip !== null ? mb_substr(trim($ip), 0, 45) : null,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,

            'created_at' => now(),
        ]);
    }
}

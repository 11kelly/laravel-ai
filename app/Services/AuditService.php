<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

class AuditService
{
    public function record(
        string $eventType,
        ?Authenticatable $actor,
        string $actorType,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $requestId = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): void {
        AuditEvent::query()->create([
            'event_type' => $eventType,
            'occurred_at' => ($occurredAt ?? now()),
            'actor_type' => $actorType,
            'actor_id' => $actor?->getAuthIdentifier(),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'request_id' => $requestId,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}



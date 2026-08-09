<?php

namespace App\Services;

use App\Models\DocumentAccessLog;
use App\Models\User;

class DocumentAccessLogger
{
    public function record(User $user, object $document, string $action, ?int $versionId, ?string $ipAddress, ?string $userAgent): void
    {
        DocumentAccessLog::query()->create([
            'user_id' => $user->id,
            'document_type' => $document::class,
            'document_id' => $document->id,
            'document_version_id' => $versionId,
            'action' => $action,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
        ]);
    }
}

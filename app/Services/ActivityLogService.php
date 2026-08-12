<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(
        string $action,
        string $description,
        ?int $storeId = null,
        ?int $userId = null,
        ?Model $subject = null,
        ?array $properties = null,
        ?string $ipAddress = null
    ): ActivityLog {
        return ActivityLog::create([
            'store_id' => $storeId ?? request()->user()?->store_id ?? 1,
            'user_id' => $userId ?? request()->user()?->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'description' => $description,
            'ip_address' => $ipAddress ?? request()->ip(),
            'properties' => $properties,
        ]);
    }
}

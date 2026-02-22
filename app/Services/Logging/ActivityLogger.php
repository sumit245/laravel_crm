<?php

namespace App\Services\Logging;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for audit trail. Called by controllers and services to record
 * significant actions (inventory imports, dispatches, task changes, etc.) with context data.
 *
 * Data Flow:
 *   Action occurs → Logger called with entity type + action + metadata → ActivityLog
 *   record created → Available in audit view
 *
 * @depends-on ActivityLog
 * @business-domain Audit & Compliance
 * @package App\Services\Logging
 */
class ActivityLogger
{
    private ?Request $request;

    /**
     * Create a new ActivityLogger instance.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  ?Request  $request  The incoming HTTP request
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Log a high-level activity entry.
     *
     * @param  string       $module
     * @param  string       $action
     * @param  Model|null   $entity
     * @param  array<string,mixed> $data
     */
    public function log(string $module, string $action, ?Model $entity = null, array $data = []): ActivityLog
    {
        $user = $this->getUser();

        $payloadChanges = $data['changes'] ?? null;
        $payloadExtra = $data['extra'] ?? null;

        // Guard against accidental sensitive payloads
        $payloadChanges = $this->sanitizePayload($payloadChanges);
        $payloadExtra = $this->sanitizePayload($payloadExtra);

        $attributes = [
            'user_id' => $user?->getAuthIdentifier(),
            'project_id' => $this->resolveProjectId($entity, $data),
            'module' => $module,
            'action' => $action,
            'entity_type' => $entity ? get_class($entity) : ($data['entity_type'] ?? null),
            'entity_id' => $entity?->getKey() ?? ($data['entity_id'] ?? null),
            'description' => $data['description'] ?? null,
            'changes' => $payloadChanges,
            'extra' => $payloadExtra,
            'ip_address' => $this->request?->ip(),
            'user_agent' => $this->request?->userAgent(),
            'request_id' => $data['request_id'] ?? null,
            'batch_id' => $data['batch_id'] ?? null,
        ];

        return ActivityLog::create($attributes);
    }

    /**
     * Build a before/after diff for a model.
     *
     * @param  Model  $model
     * @param  array<int,string>  $ignore
     * @return array<string,array<string,mixed>>
     */
    public function diff(Model $model, array $ignore = ['updated_at']): array
    {
        $original = Arr::except($model->getOriginal(), $ignore);
        $changes = Arr::except($model->getChanges(), $ignore);

        $before = [];
        $after = [];

        foreach ($changes as $key => $newValue) {
            $before[$key] = $original[$key] ?? null;
            $after[$key] = $newValue;
        }

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    /**
     * Get the user.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @return ?Authenticatable  
     */
    private function getUser(): ?Authenticatable
    {
        try {
            return Auth::user();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Best-effort project id resolution from entity or payload.
     *
     * @param  Model|null  $entity
     * @param  array<string,mixed>  $data
     */
    private function resolveProjectId(?Model $entity, array $data): ?int
    {
        if (isset($data['project_id'])) {
            return (int) $data['project_id'];
        }

        if (! $entity) {
            return null;
        }

        // Common patterns in this CRM
        foreach (['project_id', 'projectId'] as $attribute) {
            if (isset($entity->{$attribute})) {
                return (int) $entity->{$attribute};
            }
        }

        if (method_exists($entity, 'project') && $entity->relationLoaded('project')) {
            return (int) optional($entity->project)->id;
        }

        return null;
    }

    /**
     * Remove obviously sensitive keys from logged payloads.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private function sanitizePayload($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'remember_token',
        ];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $value)) {
                $value[$key] = '***';
            }
        }

        return $value;
    }
}


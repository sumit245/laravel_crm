<?php

namespace App\Services\Notification;

use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\UserEventNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EventNotificationService
{
    public function __construct(
        private readonly StakeholderResolver $stakeholderResolver
    ) {
    }

    /**
     * Persist in-app notifications for all resolved stakeholders.
     *
     * @param  string  $module
     * @param  string  $action
     * @param  Model|null  $entity
     * @param  array<string, mixed>  $data
     */
    public function notifyForActivity(string $module, string $action, ?Model $entity = null, array $data = []): void
    {
        if (!Schema::hasTable('user_event_notifications')) {
            return;
        }

        $context = $this->extractContext($entity, $data);
        $recipientIds = $this->stakeholderResolver->resolve($context['project_id'], $context['district_id']);

        if ($recipientIds->isEmpty()) {
            return;
        }

        $title = $this->buildTitle($module, $action);
        $message = $data['description'] ?? sprintf('New %s update: %s', $module, str_replace('_', ' ', $action));

        $rows = $recipientIds->map(function (int $userId) use ($module, $action, $title, $message, $context, $data, $entity) {
            return [
                'user_id' => $userId,
                'project_id' => $context['project_id'],
                'district_id' => $context['district_id'],
                'module' => $module,
                'action' => $action,
                'title' => $title,
                'message' => $message,
                'payload' => json_encode([
                    'entity_type' => $entity ? get_class($entity) : ($data['entity_type'] ?? null),
                    'entity_id' => $entity?->getKey() ?? ($data['entity_id'] ?? null),
                    'changes' => Arr::get($data, 'changes'),
                    'extra' => Arr::get($data, 'extra'),
                ]),
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        UserEventNotification::insert($rows);
    }

    /**
     * Persist in-app notifications for explicit recipient user IDs.
     *
     * @param  Collection<int, int>|array<int, int>  $recipientIds
     * @param  array<string, mixed>  $context
     */
    public function notifyUsers(
        Collection|array $recipientIds,
        string $module,
        string $action,
        string $title,
        string $message,
        array $context = [],
    ): void {
        if (! Schema::hasTable('user_event_notifications')) {
            return;
        }

        $ids = collect($recipientIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $projectId = isset($context['project_id']) ? (int) $context['project_id'] : null;
        $districtId = isset($context['district_id']) ? (int) $context['district_id'] : null;

        $rows = $ids->map(function (int $userId) use ($module, $action, $title, $message, $projectId, $districtId, $context) {
            return [
                'user_id' => $userId,
                'project_id' => $projectId,
                'district_id' => $districtId,
                'module' => $module,
                'action' => $action,
                'title' => $title,
                'message' => $message,
                'payload' => json_encode([
                    'entity_type' => $context['entity_type'] ?? null,
                    'entity_id' => $context['entity_id'] ?? null,
                    'changes' => Arr::get($context, 'changes'),
                    'extra' => Arr::get($context, 'extra'),
                ]),
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        UserEventNotification::insert($rows);
    }

    /**
     * @param  Model|null  $entity
     * @param  array<string,mixed>  $data
     * @return array{project_id: int|null, district_id: int|null}
     */
    private function extractContext(?Model $entity, array $data): array
    {
        $projectId = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $districtId = isset($data['district_id']) ? (int) $data['district_id'] : null;

        if ($entity instanceof Task) {
            $projectId ??= (int) $entity->project_id;
            $districtId ??= $entity->site ? (int) $entity->site->district : null;
        }

        if ($entity instanceof StreetlightTask) {
            $projectId ??= (int) $entity->project_id;
            $districtId ??= $this->resolveStreetlightDistrict($entity->site);
        }

        if ($entity instanceof Streetlight) {
            $projectId ??= (int) $entity->project_id;
            $districtId ??= $this->resolveStreetlightDistrict($entity);
        }

        return [
            'project_id' => $projectId,
            'district_id' => $districtId,
        ];
    }

    private function resolveStreetlightDistrict(?Streetlight $streetlight): ?int
    {
        if (!$streetlight) {
            return null;
        }

        if (is_numeric($streetlight->district)) {
            return (int) $streetlight->district;
        }

        return null;
    }

    private function buildTitle(string $module, string $action): string
    {
        return ucfirst($module) . ' ' . ucwords(str_replace('_', ' ', $action));
    }
}

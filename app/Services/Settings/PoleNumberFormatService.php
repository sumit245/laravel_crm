<?php

namespace App\Services\Settings;

use App\Jobs\RegeneratePoleNumbersJob;
use App\Models\Pole;
use App\Models\Settings\PoleNumberFormat;
use App\Models\Settings\PoleNumberRegenerationBatch;
use App\Models\StreetlightSiteWard;
use App\Models\StreetlightTask;
use Illuminate\Support\Collection;

class PoleNumberFormatService
{
    /** Run regeneration inline when affected pole count is at or below this (critical admin action). */
    public const SYNC_REGENERATION_POLE_LIMIT = 2000;

    public const TOKEN_TYPES = [
        'prefix',
        'project',
        'state',
        'district',
        'block',
        'panchayat',
        'ward_label',
        'ward_number',
        'pole_number',
    ];

    /**
     * Default token row values for the settings form (create-new state).
     *
     * @return array<string, array{enabled: bool, value: string, length: int, pad: int, separator_after: string}>
     */
    public static function defaultTokenFormState(): array
    {
        $enabledTypes = ['prefix', 'district', 'block', 'panchayat', 'ward_label', 'ward_number', 'pole_number'];
        $separatorTypes = ['prefix', 'project', 'state', 'district', 'block', 'panchayat', 'ward_number'];
        $state = [];

        foreach (self::TOKEN_TYPES as $type) {
            $state[$type] = [
                'enabled' => in_array($type, $enabledTypes, true),
                'value' => match ($type) {
                    'prefix' => 'SUG',
                    'ward_label' => 'W',
                    default => '',
                },
                'length' => 3,
                'pad' => in_array($type, ['ward_number', 'pole_number'], true) ? 2 : 0,
                'separator_after' => in_array($type, $separatorTypes, true) ? '/' : '',
            ];
        }

        return $state;
    }

    public function formats(): Collection
    {
        return PoleNumberFormat::with('project')
            ->orderByRaw('project_id is not null')
            ->orderBy('project_id')
            ->orderBy('ward_type')
            ->get();
    }

    public function resolve(?int $projectId, string $wardType): PoleNumberFormat
    {
        return PoleNumberFormat::query()
            ->where('ward_type', $wardType)
            ->where('is_active', true)
            ->where('project_id', $projectId)
            ->first()
            ?? PoleNumberFormat::query()
                ->where('ward_type', $wardType)
                ->where('is_active', true)
                ->whereNull('project_id')
                ->firstOrFail();
    }

    public function update(array $data, ?int $userId): PoleNumberFormat
    {
        $wardType = $data['ward_type'];
        $projectId = blank($data['project_id'] ?? null) ? null : (int) $data['project_id'];

        if (! empty($data['is_active'])) {
            PoleNumberFormat::query()
                ->where('ward_type', $wardType)
                ->where('project_id', $projectId)
                ->when(! empty($data['format_id']), fn ($query) => $query->where('id', '!=', $data['format_id']))
                ->update(['is_active' => false]);
        }

        $attributes = [
            'project_id' => $projectId,
            'ward_type' => $wardType,
            'name' => $data['name'],
            'tokens' => $this->normalizeTokens($data['tokens'] ?? []),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $userId,
        ];

        if (! empty($data['format_id'])) {
            $format = PoleNumberFormat::findOrFail($data['format_id']);
            $format->update($attributes);

            return $format->fresh();
        }

        return PoleNumberFormat::create($attributes);
    }

    public function preview(PoleNumberFormat $format, int $limit = 10): array
    {
        $query = $this->polesForFormat($format)->limit($limit);

        return [
            'affected_count' => $this->polesForFormat($format)->count(),
            'samples' => $query->get()->map(fn (Pole $pole) => [
                'pole_id' => $pole->id,
                'old' => $pole->complete_pole_number,
                'new' => $this->generateForPole($pole, $format),
            ])->all(),
        ];
    }

    public function createRegenerationBatch(PoleNumberFormat $format, ?int $userId): PoleNumberRegenerationBatch
    {
        $batch = PoleNumberRegenerationBatch::create([
            'pole_number_format_id' => $format->id,
            'project_id' => $format->project_id,
            'ward_type' => $format->ward_type,
            'status' => 'pending',
            'affected_count' => $this->polesForFormat($format)->count(),
            'created_by' => $userId,
        ]);

        if ($batch->affected_count <= self::SYNC_REGENERATION_POLE_LIMIT) {
            RegeneratePoleNumbersJob::dispatchSync($batch->id);
        } else {
            RegeneratePoleNumbersJob::dispatch($batch->id);
        }

        return $batch->fresh();
    }

    public function polesForFormat(PoleNumberFormat $format)
    {
        return Pole::query()
            ->with(['task.project', 'task.site', 'siteWard'])
            ->whereHas('task', function ($query) use ($format) {
                if ($format->project_id) {
                    $query->where('project_id', $format->project_id);
                }
            })
            ->where(function ($query) use ($format) {
                $query->where('ward_type', $format->ward_type)
                    ->orWhereHas('siteWard', fn ($wardQuery) => $wardQuery->where('ward_type', $format->ward_type))
                    ->when($format->ward_type === 'normal', function ($legacyQuery) {
                        $legacyQuery->orWhere(function ($query) {
                            $query->whereNull('ward_type')
                                ->where(function ($wardQuery) {
                                    $wardQuery->whereNull('ward_name')
                                        ->orWhere('ward_name', 'not like', '%GP%');
                                });
                        });
                    })
                    ->when($format->ward_type === 'gp', function ($legacyQuery) {
                        $legacyQuery->orWhere(function ($query) {
                            $query->whereNull('ward_type')
                                ->where('ward_name', 'like', '%GP%');
                        });
                    });
            });
    }

    public function generateForPole(Pole $pole, ?PoleNumberFormat $format = null): string
    {
        $task = $pole->task ?: StreetlightTask::with(['project', 'site'])->find($pole->task_id);
        $site = $task?->site;
        $ward = $pole->siteWard;
        $wardType = $ward?->ward_type ?: ($pole->ward_type ?: 'normal');

        $format ??= $this->resolve($task?->project_id, $wardType);

        $parts = [];
        foreach ($format->tokens ?? [] as $token) {
            if (empty($token['enabled'])) {
                continue;
            }

            $value = $this->tokenValue($token, $pole, $task, $site, $ward);
            if ($value === '') {
                continue;
            }

            $parts[] = $value.($token['separator_after'] ?? '');
        }

        return implode('', $parts);
    }

    public function normalizeTokens(array $tokens): array
    {
        $normalized = [];
        foreach (self::TOKEN_TYPES as $type) {
            $token = $tokens[$type] ?? [];
            $normalized[] = [
                'type' => $type,
                'enabled' => ! empty($token['enabled']),
                'value' => trim((string) ($token['value'] ?? '')),
                'length' => max(1, min(20, (int) ($token['length'] ?? 3))),
                'pad' => max(0, min(10, (int) ($token['pad'] ?? 0))),
                'separator_after' => (string) ($token['separator_after'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function tokenValue(array $token, Pole $pole, ?StreetlightTask $task, $site, ?StreetlightSiteWard $ward): string
    {
        $type = $token['type'];
        $raw = match ($type) {
            'prefix' => $token['value'] ?? '',
            'project' => $task?->project?->project_name ?? '',
            'state' => $site?->state ?? '',
            'district' => $site?->district ?? '',
            'block' => $site?->block ?? '',
            'panchayat' => $site?->panchayat ?? '',
            'ward_label' => $token['value'] ?: (($ward?->ward_type ?? $pole->ward_type) === 'gp' ? 'GP' : 'W'),
            'ward_number' => $ward?->ward_number ?? $pole->ward_number ?? preg_replace('/\D/', '', (string) $pole->ward_name),
            'pole_number' => $pole->pole_sequence ?? $this->extractPoleSequence($pole->complete_pole_number),
            default => '',
        };

        $raw = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $raw));

        if (in_array($type, ['project', 'state', 'district', 'block', 'panchayat'], true)) {
            $raw = substr($raw, 0, (int) ($token['length'] ?? 3));
        }

        if (in_array($type, ['ward_number', 'pole_number'], true) && ! empty($token['pad'])) {
            $raw = str_pad($raw, (int) $token['pad'], '0', STR_PAD_LEFT);
        }

        return $raw;
    }

    private function extractPoleSequence(?string $value): string
    {
        $last = collect(explode('/', (string) $value))->filter()->last();

        return preg_replace('/\D/', '', (string) $last) ?: '1';
    }
}

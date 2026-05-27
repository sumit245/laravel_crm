<?php

namespace App\Services\Streetlight;

use App\Models\Streetlight;
use App\Models\StreetlightSiteWard;
use App\Models\StreetlightTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiteWardService
{
    public function syncSiteWards(Streetlight $site, ?string $normalWards, ?string $gpWards, string $source = 'site'): void
    {
        DB::transaction(function () use ($site, $normalWards, $gpWards, $source) {
            $normal = $this->parseNormalWards($normalWards);
            $gp = $this->parseGpWards($gpWards);

            $this->upsertWards($site, StreetlightSiteWard::TYPE_NORMAL, $normal, 10, $source);
            $this->upsertWards($site, StreetlightSiteWard::TYPE_GP, $gp, null, $source);

            $site->forceFill([
                'ward' => $this->legacyNormalWardString($site),
                'total_poles' => $this->totalPlannedPoles($site),
            ])->save();
        });
    }

    public function ensureGpWards(Streetlight $site, ?string $gpWards, string $source = 'target'): void
    {
        $gp = $this->parseGpWards($gpWards);
        if ($gp === []) {
            return;
        }

        DB::transaction(function () use ($site, $gp, $source) {
            $this->upsertWards($site, StreetlightSiteWard::TYPE_GP, $gp, null, $source);
            $site->forceFill(['total_poles' => $this->totalPlannedPoles($site)])->save();
        });
    }

    public function optionsForSite(int $siteId, ?int $taskId = null): Collection
    {
        $site = Streetlight::with('siteWards')->find($siteId);
        if (! $site) {
            return collect();
        }

        $this->ensureLegacyBackfill($site);

        $otherTaskWardIds = DB::table('streetlight_task_wards')
            ->join('streetlight_tasks', 'streetlight_tasks.id', '=', 'streetlight_task_wards.streetlight_task_id')
            ->where('streetlight_tasks.site_id', $site->id)
            ->when($taskId, fn ($query) => $query->where('streetlight_tasks.id', '!=', $taskId))
            ->pluck('streetlight_task_wards.streetlight_site_ward_id')
            ->all();

        $currentTaskWardIds = $taskId
            ? DB::table('streetlight_task_wards')->where('streetlight_task_id', $taskId)->pluck('streetlight_site_ward_id')->all()
            : [];

        $legacyCurrentWards = [];
        if ($taskId) {
            $task = StreetlightTask::find($taskId);
            $legacyCurrentWards = $this->parseNormalWards($task?->allotted_wards);
        }

        return $site->siteWards()
            ->orderByRaw("case when ward_type = 'normal' then 0 else 1 end")
            ->orderByRaw('cast(ward_number as unsigned), ward_number')
            ->get()
            ->filter(fn (StreetlightSiteWard $ward) => ! in_array($ward->id, $otherTaskWardIds, true))
            ->map(function (StreetlightSiteWard $ward) use ($currentTaskWardIds, $legacyCurrentWards) {
                return [
                    'id' => $ward->id,
                    'key' => $ward->key_string,
                    'ward' => $ward->display_name,
                    'ward_number' => $ward->ward_number,
                    'ward_type' => $ward->ward_type,
                    'planned_poles' => $ward->planned_poles,
                    'is_currently_allotted' => in_array($ward->id, $currentTaskWardIds, true)
                        || ($ward->ward_type === StreetlightSiteWard::TYPE_NORMAL && in_array($ward->ward_number, $legacyCurrentWards, true)),
                ];
            })
            ->values();
    }

    public function syncTaskWards(StreetlightTask $task, ?string $selectedWards, ?string $gpWards = null): void
    {
        $site = Streetlight::find($task->site_id);
        if (! $site) {
            return;
        }

        $this->ensureLegacyBackfill($site);
        $this->ensureGpWards($site, $gpWards, 'target');

        $keys = collect(explode(',', (string) $selectedWards))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->map(fn ($value) => str_contains($value, ':') ? $value : 'normal:'.$value)
            ->unique()
            ->values();

        $wardIds = $keys->map(function (string $key) use ($site) {
            [$type, $number] = array_pad(explode(':', $key, 2), 2, null);

            return StreetlightSiteWard::query()
                ->where('streetlight_id', $site->id)
                ->where('ward_type', $type)
                ->where('ward_number', $number)
                ->value('id');
        })->filter()->values()->all();

        $task->siteWards()->sync($wardIds);

        $legacyNormal = StreetlightSiteWard::query()
            ->whereIn('id', $wardIds)
            ->where('ward_type', StreetlightSiteWard::TYPE_NORMAL)
            ->orderByRaw('cast(ward_number as unsigned), ward_number')
            ->pluck('ward_number')
            ->implode(',');

        $task->forceFill(['allotted_wards' => $legacyNormal])->save();
    }

    public function legacyNormalWardString(Streetlight $site): string
    {
        return $site->siteWards()
            ->where('ward_type', StreetlightSiteWard::TYPE_NORMAL)
            ->orderByRaw('cast(ward_number as unsigned), ward_number')
            ->pluck('ward_number')
            ->implode(',');
    }

    public function totalPlannedPoles(Streetlight $site): int
    {
        return (int) $site->siteWards()->sum('planned_poles');
    }

    public function ensureLegacyBackfill(Streetlight $site): void
    {
        if ($site->siteWards()->exists()) {
            return;
        }

        $this->syncSiteWards($site, $site->ward, null, 'legacy');
    }

    public function parseNormalWards(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($ward) => trim($ward))
            ->filter(fn ($ward) => $ward !== '' && preg_match('/^\d+$/', $ward))
            ->unique()
            ->values()
            ->all();
    }

    public function parseGpWards(?string $value): array
    {
        $items = collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter();

        $wards = [];
        foreach ($items as $item) {
            if (! preg_match('/^(\d+)\s*:\s*(\d+)$/', $item, $matches)) {
                throw ValidationException::withMessages([
                    'gp_wards' => 'GP wards must use format like 3:4,8:2.',
                ]);
            }

            $count = (int) $matches[2];
            if ($count < 1 || $count > 10) {
                throw ValidationException::withMessages([
                    'gp_wards' => 'Each GP ward must have 1 to 10 poles.',
                ]);
            }

            $wards[$matches[1]] = $count;
        }

        return $wards;
    }

    private function upsertWards(Streetlight $site, string $type, array $wards, ?int $fixedPoles, string $source): void
    {
        $keep = [];
        foreach ($wards as $ward => $plannedPoles) {
            if ($fixedPoles !== null && is_int($ward)) {
                $ward = (string) $plannedPoles;
                $plannedPoles = $fixedPoles;
            }

            $keep[] = (string) $ward;
            StreetlightSiteWard::updateOrCreate(
                [
                    'streetlight_id' => $site->id,
                    'ward_type' => $type,
                    'ward_number' => (string) $ward,
                ],
                [
                    'planned_poles' => $fixedPoles ?? (int) $plannedPoles,
                    'source' => $source,
                ]
            );
        }

        if ($source !== 'target') {
            $site->siteWards()
                ->where('ward_type', $type)
                ->whereNotIn('ward_number', $keep ?: ['__none__'])
                ->doesntHave('tasks')
                ->delete();
        }
    }
}

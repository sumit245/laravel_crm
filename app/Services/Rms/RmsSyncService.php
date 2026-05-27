<?php

namespace App\Services\Rms;

use App\Jobs\ProcessRmsSyncChunk;
use App\Models\Pole;
use App\Models\RmsSyncBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RmsSyncService
{
    public function queueSync(array $scope, ?int $requestedBy = null, ?string $projectName = null): RmsSyncBatch
    {
        $requestedBy = $requestedBy ?? Auth::id();
        $query = $this->buildPoleQuery($scope);
        $totalPoles = (clone $query)->count();

        $batch = RmsSyncBatch::create([
            'source' => $scope['source'] ?? 'rms_sync',
            'scope' => $scope,
            'requested_by' => $requestedBy,
            'status' => $totalPoles > 0 ? 'queued' : 'completed',
            'total_poles' => $totalPoles,
            'finished_at' => $totalPoles > 0 ? null : now(),
        ]);

        if ($totalPoles === 0) {
            return $batch;
        }

        (clone $query)
            ->select('id')
            ->orderBy('id')
            ->chunk(300, function ($rows) use ($batch, $requestedBy, $projectName) {
                $poleIds = $rows->pluck('id')->all();
                ProcessRmsSyncChunk::dispatch($batch->id, $poleIds, $requestedBy, $projectName);
            });

        return $batch;
    }

    private function buildPoleQuery(array $scope): Builder
    {
        $query = Pole::query();
        $filter = $scope['filter'] ?? 'all';

        if (!empty($scope['installed_only'])) {
            $query->where('isInstallationDone', true);
        } elseif ($filter === 'surveyed') {
            $query->where('isSurveyDone', true);
        } elseif ($filter === 'installed') {
            $query->where('isInstallationDone', true);
        }

        if (
            !empty($scope['project_id']) ||
            !empty($scope['district']) ||
            !empty($scope['block']) ||
            !empty($scope['panchayat'])
        ) {
            $query->whereHas('task.site', function (Builder $siteQuery) use ($scope) {
                if (!empty($scope['project_id'])) {
                    $siteQuery->where('project_id', $scope['project_id']);
                }
                if (!empty($scope['district'])) {
                    $siteQuery->where('district', $scope['district']);
                }
                if (!empty($scope['block'])) {
                    $siteQuery->where('block', $scope['block']);
                }
                if (!empty($scope['panchayat'])) {
                    $siteQuery->where('panchayat', $scope['panchayat']);
                }
            });
        }

        return $query;
    }
}


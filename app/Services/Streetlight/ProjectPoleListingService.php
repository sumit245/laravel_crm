<?php

namespace App\Services\Streetlight;

use App\Models\Pole;
use App\Models\Streetlight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProjectPoleListingService
{
    public function districtsForProject(int $projectId): Collection
    {
        return Streetlight::query()
            ->where('project_id', $projectId)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->get();
    }

    public function installedQuery(Request $request, int $projectId): Builder
    {
        $query = Pole::query()->where('isInstallationDone', 1);

        $this->applyProjectScope($query, $projectId);
        $this->applyGeoFilters($query, $request);
        $this->applySharedTaskFilters($query, $request);
        $this->applyBilledFilter($query, $request);

        return $query;
    }

    public function surveyedQuery(Request $request, int $projectId): Builder
    {
        $query = Pole::query()->where('isSurveyDone', 1);

        $this->applyProjectScope($query, $projectId);
        $this->applyGeoFilters($query, $request);
        $this->applySharedTaskFilters($query, $request);
        $this->applyInstallationFilter($query, $request);
        $this->applyBilledFilter($query, $request);

        return $query;
    }

    public function geoFilterKeys(): array
    {
        return ['district', 'block', 'panchayat'];
    }

    public function statusFilterKeysForInstalled(): array
    {
        return ['filter_billed', 'filter_rms'];
    }

    public function statusFilterKeysForSurveyed(): array
    {
        return ['filter_installed', 'filter_billed', 'filter_rms'];
    }

    public function activeGeoFilters(Request $request): array
    {
        return $request->only($this->geoFilterKeys());
    }

    public function activeStatusFilters(Request $request, string $tab): array
    {
        $keys = $tab === 'surveyed'
            ? $this->statusFilterKeysForSurveyed()
            : $this->statusFilterKeysForInstalled();

        return $request->only($keys);
    }

    public function queryStringExceptHash(Request $request, array $except = []): string
    {
        $params = $request->except(array_merge(['_token'], $except));
        $qs = http_build_query($params);

        return $qs ? '?' . $qs : '';
    }

    private function applyProjectScope(Builder $query, int $projectId): void
    {
        $query->whereHas('task', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    private function applyGeoFilters(Builder $query, Request $request): void
    {
        if ($request->filled('district')) {
            $query->whereHas('task.site', fn ($q) => $q->where('district', $request->district));
        }

        if ($request->filled('block')) {
            $query->whereHas('task.site', fn ($q) => $q->where('block', $request->block));
        }

        if ($request->filled('panchayat')) {
            $query->whereHas('task.site', fn ($q) => $q->where('panchayat', $request->panchayat));
        }
    }

    private function applySharedTaskFilters(Builder $query, Request $request): void
    {
        if ($request->filled('project_manager')) {
            $query->whereHas('task', fn ($q) => $q->where('manager_id', $request->project_manager));
        }

        if ($request->filled('site_engineer')) {
            $query->whereHas('task', fn ($q) => $q->where('engineer_id', $request->site_engineer));
        }

        if ($request->filled('vendor')) {
            $query->whereHas('task', fn ($q) => $q->where('vendor_id', $request->vendor));
        }

        if ($request->filled('ward')) {
            $query->whereHas('task.site', fn ($q) => $q->where('ward', 'like', '%' . $request->ward . '%'));
        }
    }

    private function applyBilledFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('filter_billed')) {
            return;
        }

        if ($request->filter_billed == '1') {
            $query->whereHas('task', fn ($q) => $q->where('billed', 1));
        } elseif ($request->filter_billed == '0') {
            $query->whereHas('task', function ($q) {
                $q->where('billed', 0)->orWhereNull('billed');
            });
        }
    }

    private function applyInstallationFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('filter_installed')) {
            return;
        }

        $query->where('isInstallationDone', $request->filter_installed == '1' ? 1 : 0);
    }

    public function applyRmsFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('filter_rms')) {
            return;
        }

        $status = $request->filter_rms;

        if ($status === 'pending') {
            $query->whereDoesntHave('rmsLogs');

            return;
        }

        $query->whereHas('rmsLogs');

        if ($status === 'success') {
            $query->whereDoesntHave('rmsLogs', fn ($q) => $q->where('status', 'error'));
        } elseif ($status === 'error') {
            $query->whereHas('rmsLogs', fn ($q) => $q->where('status', 'error'));
        } elseif ($status === 'partial') {
            $query->whereHas('rmsLogs', fn ($q) => $q->where('status', 'error'))
                ->whereHas('rmsLogs', fn ($q) => $q->where('status', 'success'));
        }
    }

    public function applyGlobalSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('complete_pole_number', 'like', "%{$search}%")
                ->orWhere('luminary_qr', 'like', "%{$search}%")
                ->orWhere('sim_number', 'like', "%{$search}%")
                ->orWhere('battery_qr', 'like', "%{$search}%")
                ->orWhere('panel_qr', 'like', "%{$search}%")
                ->orWhereHas('task.site', function ($sq) use ($search) {
                    $sq->where('district', 'like', "%{$search}%")
                        ->orWhere('block', 'like', "%{$search}%")
                        ->orWhere('panchayat', 'like', "%{$search}%");
                });
        });
    }

    public function preserveDeepLinkParams(Request $request): array
    {
        return $request->only([
            'project_manager',
            'site_engineer',
            'vendor',
            'ward',
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function installedExportHeadings(): array
    {
        return [
            'Pole Number',
            'Beneficiary',
            'Beneficiary Contact',
            'District',
            'Block',
            'Panchayat',
            'IMEI',
            'SIM Number',
            'Battery',
            'Panel',
            'Bill Raised',
            'RMS Status',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function surveyedExportHeadings(): array
    {
        return [
            'Pole Number',
            'Beneficiary',
            'Beneficiary Contact',
            'District',
            'Block',
            'Panchayat',
            'IMEI',
            'SIM Number',
            'Battery',
            'Panel',
            'Installed',
            'Bill Raised',
            'RMS Status',
        ];
    }

    public function mapInstalledPoleForExport(Pole $pole): array
    {
        return [
            $pole->complete_pole_number ?? '',
            $pole->beneficiary ?? '',
            $pole->beneficiary_contact ?? '',
            $pole->task?->site?->district ?? '',
            $pole->task?->site?->block ?? '',
            $pole->task?->site?->panchayat ?? '',
            $pole->luminary_qr ?? '',
            $pole->sim_number ?? '',
            $pole->battery_qr ?? '',
            $pole->panel_qr ?? '',
            ($pole->task && $pole->task->billed) ? 'Yes' : 'No',
            $this->rmsStatusLabel($pole),
        ];
    }

    public function mapSurveyedPoleForExport(Pole $pole): array
    {
        return [
            $pole->complete_pole_number ?? '',
            $pole->beneficiary ?? '',
            $pole->beneficiary_contact ?? '',
            $pole->task?->site?->district ?? '',
            $pole->task?->site?->block ?? '',
            $pole->task?->site?->panchayat ?? '',
            $pole->luminary_qr ?? '',
            $pole->sim_number ?? '',
            $pole->battery_qr ?? '',
            $pole->panel_qr ?? '',
            $pole->isInstallationDone ? 'Yes' : 'No',
            ($pole->task && $pole->task->billed) ? 'Yes' : 'No',
            $this->rmsStatusLabel($pole),
        ];
    }

    public function rmsStatusLabel(Pole $pole): string
    {
        $rmsLogs = $pole->rmsLogs ?? collect();
        $success = $rmsLogs->where('status', 'success')->count();
        $errors = $rmsLogs->where('status', 'error')->count();
        $total = $success + $errors;

        if ($total === 0) {
            return 'Not Pushed';
        }

        if ($errors > 0 && $success > 0) {
            return 'Partial';
        }

        if ($errors > 0) {
            return 'Error';
        }

        return 'Success';
    }

    public function formatRmsStatusCell(Pole $pole): string
    {
        $rmsLogs = $pole->rmsLogs ?? collect();
        $rmsSuccessCount = $rmsLogs->where('status', 'success')->count();
        $rmsErrorCount = $rmsLogs->where('status', 'error')->count();
        $rmsTotal = $rmsSuccessCount + $rmsErrorCount;
        $rmsStatus = $rmsTotal > 0 ? ($rmsErrorCount > 0 ? 'partial' : 'success') : 'pending';

        if ($rmsTotal > 0) {
            $successPct = $rmsTotal > 0 ? ($rmsSuccessCount / $rmsTotal * 100) : 0;
            $errorPct = $rmsTotal > 0 ? ($rmsErrorCount / $rmsTotal * 100) : 0;

            return '<div class="rms-status-indicator" data-rms-status="' . e($rmsStatus) . '" data-success="' . (int) $rmsSuccessCount . '" data-error="' . (int) $rmsErrorCount . '" data-total="' . (int) $rmsTotal . '" data-pole-id="' . (int) $pole->id . '" style="cursor: pointer;">'
                . '<div class="rms-progress-bar">'
                . '<div class="rms-success-bar" style="width: ' . $successPct . '%"></div>'
                . '<div class="rms-error-bar" style="width: ' . $errorPct . '%"></div>'
                . '</div>'
                . '<small class="rms-status-text">' . (int) $rmsSuccessCount . '/' . (int) $rmsTotal . ' Success</small>'
                . '</div>';
        }

        return '<span class="badge badge-readable badge-not-pushed" data-rms-status="pending">Not Pushed</span>';
    }
}

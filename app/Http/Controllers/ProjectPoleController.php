<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesColumnFilters;
use App\Http\Controllers\Concerns\HandlesDataTableExport;
use App\Http\Requests\DataTableExportRequest;
use App\Models\Pole;
use App\Models\Project;
use App\Services\Streetlight\ProjectPoleListingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectPoleController extends Controller
{
    use HandlesColumnFilters;
    use HandlesDataTableExport;

    public function __construct(
        private readonly ProjectPoleListingService $poleListing
    ) {}

    public function installedData(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->buildDataTableResponse(
            $request,
            $this->filteredInstalledQuery($request, (int) $project->id, false),
            'installed'
        );
    }

    public function surveyedData(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->buildDataTableResponse(
            $request,
            $this->filteredSurveyedQuery($request, (int) $project->id, false),
            'surveyed'
        );
    }

    public function installedExport(Project $project, DataTableExportRequest $request): BinaryFileResponse
    {
        $this->authorize('view', $project);

        $query = $this->filteredInstalledQuery($request, (int) $project->id);
        $filename = 'installed_poles_' . $project->id . '_' . now()->format('Y-m-d') . '.xlsx';

        return $this->downloadDataTableExport(
            $request,
            $query,
            $this->poleListing->installedExportHeadings(),
            fn (Pole $pole) => $this->poleListing->mapInstalledPoleForExport($pole),
            $filename
        );
    }

    public function surveyedExport(Project $project, DataTableExportRequest $request): BinaryFileResponse
    {
        $this->authorize('view', $project);

        $query = $this->filteredSurveyedQuery($request, (int) $project->id);
        $filename = 'surveyed_poles_' . $project->id . '_' . now()->format('Y-m-d') . '.xlsx';

        return $this->downloadDataTableExport(
            $request,
            $query,
            $this->poleListing->surveyedExportHeadings(),
            fn (Pole $pole) => $this->poleListing->mapSurveyedPoleForExport($pole),
            $filename
        );
    }

    private function filteredInstalledQuery(Request $request, int $projectId, bool $withTableSearch = true): Builder
    {
        $query = $this->poleListing->installedQuery($request, $projectId)
            ->with(['task.site', 'rmsLogs']);

        $this->poleListing->applyRmsFilter($query, $request);

        if ($withTableSearch) {
            $this->applyPoleTableFilters($request, $query);
        }

        return $query;
    }

    private function filteredSurveyedQuery(Request $request, int $projectId, bool $withTableSearch = true): Builder
    {
        $query = $this->poleListing->surveyedQuery($request, $projectId)
            ->with(['task.site', 'rmsLogs']);

        $this->poleListing->applyRmsFilter($query, $request);

        if ($withTableSearch) {
            $this->applyPoleTableFilters($request, $query);
        }

        return $query;
    }

    private function applyPoleTableFilters(Request $request, Builder $query): Builder
    {
        if ($request->filled('search.value')) {
            $this->poleListing->applyGlobalSearch($query, $request->input('search.value'));
        }

        $cfColMap = [
            1 => 'complete_pole_number',
            2 => 'beneficiary',
            3 => 'beneficiary_contact',
            7 => 'luminary_qr',
            8 => 'sim_number',
            9 => 'battery_qr',
            10 => 'panel_qr',
        ];

        $columnFilters = $this->parseColumnFilters($request);
        $this->applyColumnFilters($query, $columnFilters, $cfColMap);
        $this->applyPoleSiteColumnFilters($query, $columnFilters);

        return $query;
    }

    private function buildDataTableResponse(Request $request, Builder $query, string $tab): JsonResponse
    {
        $totalRecords = (clone $query)->count();

        $this->applyPoleTableFilters($request, $query);

        $filteredRecords = $query->count();

        $orderColumn = (int) $request->input('order.0.column', 3);
        $orderDirection = $request->input('order.0.dir', 'asc');
        $columns = [
            'id',
            'complete_pole_number',
            'beneficiary',
            'beneficiary_contact',
            'luminary_qr',
            'sim_number',
            'battery_qr',
            'panel_qr',
            'billed',
            'rms',
        ];

        if (isset($columns[$orderColumn]) && ! in_array($columns[$orderColumn], ['billed', 'rms'], true)) {
            $poleTable = (new Pole())->getTable();
            $query->orderBy($poleTable . '.' . $columns[$orderColumn], $orderDirection);
        }

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 50);

        if ($length === -1) {
            $poles = $query->get();
        } else {
            $poles = $query->skip($start)->take($length)->get();
        }

        $poles->load(['task.site', 'rmsLogs']);

        $data = $poles->map(fn (Pole $pole) => $tab === 'surveyed'
            ? $this->formatSurveyedRow($pole)
            : $this->formatInstalledRow($pole));

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    private function formatInstalledRow(Pole $pole): array
    {
        $poleNumber = e($pole->complete_pole_number ?? 'N/A');
        if ($pole->lat && $pole->lng) {
            $poleNumber = '<span class="text-primary" style="cursor:pointer;" onclick="projectPoleLocateOnMap(' . (float) $pole->lat . ', ' . (float) $pole->lng . ')">' . $poleNumber . '</span>';
        }

        $billRaised = $pole->task && $pole->task->billed
            ? '<span class="badge badge-success">Yes</span>'
            : '<span class="badge badge-readable badge-no">No</span>';

        return [
            '<input type="checkbox" class="row-checkbox" value="' . (int) $pole->id . '" />',
            $poleNumber,
            e($pole->beneficiary ?? 'N/A'),
            e($pole->beneficiary_contact ?? 'N/A'),
            e($pole->task?->site?->district ?? 'N/A'),
            e($pole->task?->site?->block ?? 'N/A'),
            e($pole->task?->site?->panchayat ?? 'N/A'),
            e($pole->luminary_qr ?? 'N/A'),
            e($pole->sim_number ?? 'N/A'),
            e($pole->battery_qr ?? 'N/A'),
            e($pole->panel_qr ?? 'N/A'),
            $billRaised,
            $this->poleListing->formatRmsStatusCell($pole),
            $this->formatPoleActions($pole),
        ];
    }

    private function formatSurveyedRow(Pole $pole): array
    {
        $poleNumber = e($pole->complete_pole_number ?? 'N/A');
        if ($pole->lat && $pole->lng) {
            $poleNumber = '<span class="text-primary" style="cursor:pointer;" onclick="projectPoleLocateOnMap(' . (float) $pole->lat . ', ' . (float) $pole->lng . ')">' . $poleNumber . '</span>';
        }

        $billRaised = $pole->task && $pole->task->billed
            ? '<span class="badge badge-success">Yes</span>'
            : '<span class="badge badge-readable badge-no">No</span>';

        $installedBadge = $pole->isInstallationDone
            ? '<span class="badge badge-success">Yes</span>'
            : '<span class="badge badge-readable badge-no">No</span>';

        return [
            '<input type="checkbox" class="row-checkbox" value="' . (int) $pole->id . '" />',
            $poleNumber,
            e($pole->beneficiary ?? 'N/A'),
            e($pole->beneficiary_contact ?? 'N/A'),
            e($pole->task?->site?->district ?? 'N/A'),
            e($pole->task?->site?->block ?? 'N/A'),
            e($pole->task?->site?->panchayat ?? 'N/A'),
            e($pole->luminary_qr ?? 'N/A'),
            e($pole->sim_number ?? 'N/A'),
            e($pole->battery_qr ?? 'N/A'),
            e($pole->panel_qr ?? 'N/A'),
            $installedBadge,
            $billRaised,
            $this->poleListing->formatRmsStatusCell($pole),
            $this->formatPoleActions($pole),
        ];
    }

    private function applyPoleSiteColumnFilters(Builder $query, array $filters): void
    {
        $siteFields = [
            4 => 'district',
            5 => 'block',
            6 => 'panchayat',
        ];

        foreach ($siteFields as $colIdx => $field) {
            if (! isset($filters[$colIdx])) {
                continue;
            }

            $filter = $filters[$colIdx];
            $type1 = $filter['type1'] ?? 'contains';
            $val1 = trim($filter['val1'] ?? '');

            if ($val1 === '' && ! in_array($type1, ['is_empty', 'is_not_empty'], true)) {
                continue;
            }

            $query->whereHas('task.site', function ($siteQuery) use ($field, $filter) {
                $this->applyColumnFilters($siteQuery, [0 => $filter], [0 => $field]);
            });
        }
    }

    private function formatPoleActions(Pole $pole): string
    {
        $name = e($pole->complete_pole_number ?? 'this pole');

        return '
            <a href="' . route('poles.show', $pole->id) . '" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                <i class="mdi mdi-eye"></i>
            </a>
            <a href="' . route('poles.edit', $pole->id) . '" class="btn btn-icon btn-warning">
                <i class="mdi mdi-pencil"></i>
            </a>
            <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
                title="Delete Pole" data-id="' . (int) $pole->id . '"
                data-name="' . $name . '"
                data-url="' . route('poles.destroy', $pole->id) . '">
                <i class="mdi mdi-delete"></i>
            </button>
        ';
    }
}

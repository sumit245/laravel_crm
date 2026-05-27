<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\DataTableExportScope;
use App\Exports\QueryMappedExport;
use App\Http\Requests\DataTableExportRequest;
use App\Services\Export\DataTableExportService;
use Closure;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HandlesDataTableExport
{
    /**
     * @param  mixed  $query
     * @param  array<int, string>  $headings
     * @param  Closure(mixed): array<int, mixed>  $mapper
     */
    protected function downloadDataTableExport(
        DataTableExportRequest $request,
        $query,
        array $headings,
        Closure $mapper,
        string $filename,
        ?int $maxRows = null
    ): BinaryFileResponse {
        $service = app(DataTableExportService::class);
        $limit = $maxRows ?? DataTableExportService::DEFAULT_MAX_ROWS;
        $scope = $request->exportScope();

        $scopedQuery = $service->applyExportScope(clone $query, $request, $limit);

        if (! in_array($scope, [
            DataTableExportScope::CurrentPage,
            DataTableExportScope::PageRange,
            DataTableExportScope::RowLimit,
        ], true)) {
            $service->assertWithinRowLimit($scopedQuery, $limit);
        }

        return Excel::download(
            new QueryMappedExport($scopedQuery, $headings, $mapper),
            $filename
        );
    }
}

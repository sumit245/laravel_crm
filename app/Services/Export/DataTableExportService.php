<?php

namespace App\Services\Export;

use App\Enums\DataTableExportScope;
use App\Http\Requests\DataTableExportRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class DataTableExportService
{
    public const DEFAULT_MAX_ROWS = 50000;

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     * @return EloquentBuilder|QueryBuilder
     */
    public function applyExportScope($query, Request $request, int $maxRows = self::DEFAULT_MAX_ROWS)
    {
        $scope = $request instanceof DataTableExportRequest
            ? $request->exportScope()
            : (DataTableExportScope::tryFrom((string) $request->input('export_scope', 'filtered_all'))
                ?? DataTableExportScope::FilteredAll);

        $pageLength = max(1, (int) $request->input('length', 50));

        switch ($scope) {
            case DataTableExportScope::CurrentPage:
                $start = (int) $request->input('start', 0);
                $query->skip($start)->take($pageLength);
                break;

            case DataTableExportScope::PageRange:
                $pageFrom = max(1, (int) $request->input('page_from', 1));
                $pageTo = max($pageFrom, (int) $request->input('page_to', $pageFrom));
                $skip = ($pageFrom - 1) * $pageLength;
                $take = ($pageTo - $pageFrom + 1) * $pageLength;
                $query->skip($skip)->take($take);
                break;

            case DataTableExportScope::RowLimit:
                $limit = min(
                    max(1, (int) $request->input('max_rows', 1000)),
                    $maxRows
                );
                $query->limit($limit);
                break;

            case DataTableExportScope::DateRange:
                $column = $request->input('date_column');
                if ($column && $request->filled('date_from')) {
                    $query->whereDate($column, '>=', $request->input('date_from'));
                }
                if ($column && $request->filled('date_to')) {
                    $query->whereDate($column, '<=', $request->input('date_to'));
                }
                break;

            case DataTableExportScope::AllRecords:
                // Intentionally no extra scope constraints — caller must authorize.
                break;

            case DataTableExportScope::FilteredAll:
            default:
                $cap = min(
                    max(1, (int) $request->input('max_rows', $maxRows)),
                    $maxRows
                );
                if ($request->filled('max_rows')) {
                    $query->limit($cap);
                }
                break;
        }

        return $query;
    }

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    public function assertWithinRowLimit($query, int $maxRows = self::DEFAULT_MAX_ROWS): void
    {
        $count = (clone $query)->count();

        if ($count > $maxRows) {
            throw new RuntimeException(
                "Export would include {$count} rows, which exceeds the limit of {$maxRows}. Narrow your filters or use a smaller page range.",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}

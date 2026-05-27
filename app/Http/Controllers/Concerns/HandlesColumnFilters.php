<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Provides server-side support for the <x-datatable> per-column filter popup.
 *
 * The DataTables component sends a `column_filters` JSON parameter on every AJAX
 * request for server-side tables.  Structure:
 *
 *   {
 *     "2": { "type1": "contains", "val1": "9820", "connector": "and",
 *            "type2": "contains", "val2": "" },
 *     ...
 *   }
 *
 * Key = DataTables DOM column index (0-based, checkbox at 0 when bulkDelete enabled).
 *
 * Usage in a controller method:
 *
 *   use App\Http\Controllers\Concerns\HandlesColumnFilters;
 *
 *   $colMap = [1 => 'serial_number', 2 => 'item', ...];  // null entries are skipped
 *   $filters = $this->parseColumnFilters($request);
 *   $anyApplied = $this->applyColumnFilters($query, $filters, $colMap);
 *   if ($anyApplied) { $isFiltered = true; }
 *
 * For computed / aliased columns (e.g. vendor_name = CONCAT(...)) that cannot be
 * expressed as a plain column name, use applyColumnFilterRaw() instead with a
 * SQL expression string.
 */
trait HandlesColumnFilters
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Decode the column_filters JSON from the request.
     * Always returns an array (empty when absent or malformed).
     */
    protected function parseColumnFilters(Request $request): array
    {
        $raw     = $request->input('column_filters', '{}');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Apply simple column filters (column name → direct WHERE clause).
     *
     * @param  mixed  $query    Eloquent\Builder or Query\Builder
     * @param  array  $filters  From parseColumnFilters()
     * @param  array  $colMap   int $colIdx => string $dbColumn  (null entries skipped)
     * @return bool             true if at least one filter was applied
     */
    protected function applyColumnFilters($query, array $filters, array $colMap): bool
    {
        $applied = false;

        foreach ($filters as $colIdx => $filter) {
            $col = $colMap[(int) $colIdx] ?? null;
            if (! $col) {
                continue;
            }

            if ($this->buildColumnFilter($query, $col, $filter)) {
                $applied = true;
            }
        }

        return $applied;
    }

    /**
     * Apply a column filter using a raw SQL expression (for computed/aliased cols).
     *
     * Example:
     *   $expr = "CONCAT(COALESCE(vendor.firstName,''),' ',COALESCE(vendor.lastName,''))";
     *   $this->applyColumnFilterRaw($query, $expr, $filter);
     *
     * @param  mixed   $query   Eloquent\Builder or Query\Builder
     * @param  string  $expr    Raw SQL expression
     * @param  array   $filter  Single filter entry from parseColumnFilters()
     * @return bool             true if the filter was applied
     */
    protected function applyColumnFilterRaw($query, string $expr, array $filter): bool
    {
        $type1 = $filter['type1'] ?? 'contains';
        $val1  = trim($filter['val1'] ?? '');

        if ($val1 === '' && ! in_array($type1, ['is_empty', 'is_not_empty'], true)) {
            return false;
        }

        $connector = $filter['connector'] ?? 'and';
        $type2     = $filter['type2'] ?? 'contains';
        $val2      = trim($filter['val2'] ?? '');
        $hasC2     = $val2 !== '' || in_array($type2, ['is_empty', 'is_not_empty'], true);

        $query->where(function ($q) use ($expr, $type1, $val1, $connector, $type2, $val2, $hasC2) {
            $this->rawCondition($q, false, $expr, $type1, $val1);
            if ($hasC2) {
                $this->rawCondition($q, $connector === 'or', $expr, $type2, $val2);
            }
        });

        return true;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Build a WHERE group for one column filter entry.
     */
    private function buildColumnFilter($query, string $col, array $filter): bool
    {
        $type1 = $filter['type1'] ?? 'contains';
        $val1  = trim($filter['val1'] ?? '');

        // Skip if no meaningful value and operator doesn't act on emptiness
        if ($val1 === '' && ! in_array($type1, ['is_empty', 'is_not_empty'], true)) {
            return false;
        }

        $connector = $filter['connector'] ?? 'and';
        $type2     = $filter['type2'] ?? 'contains';
        $val2      = trim($filter['val2'] ?? '');
        $hasC2     = $val2 !== '' || in_array($type2, ['is_empty', 'is_not_empty'], true);

        $query->where(function ($q) use ($col, $type1, $val1, $connector, $type2, $val2, $hasC2) {
            $this->columnCondition($q, false, $col, $type1, $val1);
            if ($hasC2) {
                $this->columnCondition($q, $connector === 'or', $col, $type2, $val2);
            }
        });

        return true;
    }

    /**
     * Apply one condition on a plain column (uses standard query builder methods).
     *
     * @param  bool  $or  true → use orWhere variants, false → use where variants
     */
    private function columnCondition($q, bool $or, string $col, string $op, string $val): void
    {
        if ($val === '' && ! in_array($op, ['is_empty', 'is_not_empty'], true)) {
            return;
        }

        switch ($op) {
            case 'contains':
                $or ? $q->orWhere($col, 'LIKE', '%'.$val.'%')
                     : $q->where($col, 'LIKE', '%'.$val.'%');
                break;

            case 'starts_with':
                $or ? $q->orWhere($col, 'LIKE', $val.'%')
                     : $q->where($col, 'LIKE', $val.'%');
                break;

            case 'ends_with':
                $or ? $q->orWhere($col, 'LIKE', '%'.$val)
                     : $q->where($col, 'LIKE', '%'.$val);
                break;

            case 'equals': case 'eq': case 'is': case 'date_equals':
                $or ? $q->orWhere($col, '=', $val)
                     : $q->where($col, '=', $val);
                break;

            case 'not_equals': case 'neq': case 'is_not':
                $or ? $q->orWhere($col, '!=', $val)
                     : $q->where($col, '!=', $val);
                break;

            case 'gt':
                $or ? $q->orWhere($col, '>', $val) : $q->where($col, '>', $val);
                break;
            case 'gte':
                $or ? $q->orWhere($col, '>=', $val) : $q->where($col, '>=', $val);
                break;
            case 'lt':
                $or ? $q->orWhere($col, '<', $val) : $q->where($col, '<', $val);
                break;
            case 'lte':
                $or ? $q->orWhere($col, '<=', $val) : $q->where($col, '<=', $val);
                break;

            case 'before':
                $or ? $q->orWhere($col, '<', $val) : $q->where($col, '<', $val);
                break;
            case 'after':
                $or ? $q->orWhere($col, '>', $val) : $q->where($col, '>', $val);
                break;

            case 'is_empty':
                $closure = fn ($sq) => $sq->whereNull($col)->orWhere($col, '');
                $or ? $q->orWhere($closure) : $q->where($closure);
                break;

            case 'is_not_empty':
                $closure = fn ($sq) => $sq->whereNotNull($col)->where($col, '!=', '');
                $or ? $q->orWhere($closure) : $q->where($closure);
                break;
        }
    }

    /**
     * Apply one condition on a raw SQL expression.
     *
     * @param  bool  $or  true → orWhereRaw, false → whereRaw
     */
    private function rawCondition($q, bool $or, string $expr, string $op, string $val): void
    {
        if ($val === '' && ! in_array($op, ['is_empty', 'is_not_empty'], true)) {
            return;
        }

        switch ($op) {
            case 'contains':
                $or ? $q->orWhereRaw("{$expr} LIKE ?", ['%'.$val.'%'])
                     : $q->whereRaw("{$expr} LIKE ?", ['%'.$val.'%']);
                break;
            case 'starts_with':
                $or ? $q->orWhereRaw("{$expr} LIKE ?", [$val.'%'])
                     : $q->whereRaw("{$expr} LIKE ?", [$val.'%']);
                break;
            case 'ends_with':
                $or ? $q->orWhereRaw("{$expr} LIKE ?", ['%'.$val])
                     : $q->whereRaw("{$expr} LIKE ?", ['%'.$val]);
                break;
            case 'equals': case 'eq': case 'is': case 'date_equals':
                $or ? $q->orWhereRaw("{$expr} = ?", [$val])
                     : $q->whereRaw("{$expr} = ?", [$val]);
                break;
            case 'not_equals': case 'neq': case 'is_not':
                $or ? $q->orWhereRaw("{$expr} != ?", [$val])
                     : $q->whereRaw("{$expr} != ?", [$val]);
                break;
            case 'gt':
                $or ? $q->orWhereRaw("{$expr} > ?", [$val]) : $q->whereRaw("{$expr} > ?", [$val]);
                break;
            case 'gte':
                $or ? $q->orWhereRaw("{$expr} >= ?", [$val]) : $q->whereRaw("{$expr} >= ?", [$val]);
                break;
            case 'lt':
                $or ? $q->orWhereRaw("{$expr} < ?", [$val]) : $q->whereRaw("{$expr} < ?", [$val]);
                break;
            case 'lte':
                $or ? $q->orWhereRaw("{$expr} <= ?", [$val]) : $q->whereRaw("{$expr} <= ?", [$val]);
                break;
            case 'before':
                $or ? $q->orWhereRaw("{$expr} < ?", [$val]) : $q->whereRaw("{$expr} < ?", [$val]);
                break;
            case 'after':
                $or ? $q->orWhereRaw("{$expr} > ?", [$val]) : $q->whereRaw("{$expr} > ?", [$val]);
                break;
            case 'is_empty':
                $or ? $q->orWhereRaw("({$expr} IS NULL OR {$expr} = '')")
                     : $q->whereRaw("({$expr} IS NULL OR {$expr} = '')");
                break;
            case 'is_not_empty':
                $or ? $q->orWhereRaw("({$expr} IS NOT NULL AND {$expr} != '')")
                     : $q->whereRaw("({$expr} IS NOT NULL AND {$expr} != '')");
                break;
        }
    }
}

<?php

namespace App\Exports;

use Closure;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QueryMappedExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @param  mixed  $query  Eloquent or query builder
     * @param  array<int, string>  $headings
     * @param  Closure(mixed): array<int, mixed>  $mapper
     */
    public function __construct(
        protected $query,
        protected array $headings,
        protected Closure $mapper
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return ($this->mapper)($row);
    }
}

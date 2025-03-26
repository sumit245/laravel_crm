<?php

namespace App\Helpers;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ExcelExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;
    protected $headings;

    public function __construct(array $data, array $headings)
    {
        $this->data = $data;
        $this->headings = $headings;
    }

    public function collection()
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

class ExcelHelper
{
    /**
     * Export data to an Excel file and return the download response.
     *
     * @param array $data Array of objects or associative arrays.
     * @param string $filename The name of the Excel file.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function exportToExcel(array $data, string $filename = 'export.xlsx')
    {
        if (empty($data)) {
            return response()->json(['message' => 'No data available to export'], 400);
        }

        // Extract headers from keys of the first element
        $headings = array_keys((array) $data[0]);

        // Convert objects to arrays
        $formattedData = array_map(function ($item) {
            return (array) $item;
        }, $data);

        return Excel::download(new ExcelExport($formattedData, $headings), $filename);
    }
}

<?php

namespace App\Helpers;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelSheetExport implements FromCollection, WithHeadings, ShouldAutoSize
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

class MultiSheetExport implements WithMultipleSheets
{
    protected $sheets;

    public function __construct(array $sheets)
    {
        $this->sheets = $sheets;
    }

    public function sheets(): array
    {
        $sheetArray = [];
        foreach ($this->sheets as $sheetName => $data) {
            if (!empty($data)) {
                $headings = array_keys((array) $data[0]);
                $sheetArray[] = new ExcelSheetExport($data, $headings);
            } else {
                $sheetArray[] = new ExcelSheetExport([], []);
            }
        }
        return $sheetArray;
    }
}

class ExcelHelper
{
    /**
     * Export a single dataset to an Excel file.
     *
     * @param array|Collection $data Array or Collection of data objects/arrays
     * @param string $filename The name of the Excel file.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function exportToExcel($data, string $filename = 'export.xlsx')
    {
        if (empty($data)) {
            return response()->json(['message' => 'No data available to export'], 400);
        }

        // Convert to array if Collection
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // Get headings from first row
        $headings = !empty($data) ? array_keys((array) $data[0]) : [];

        return Excel::download(new ExcelSheetExport($data, $headings), $filename);
    }

    /**
     * Export multiple datasets to an Excel file with multiple sheets.
     *
     * @param array $sheets Associative array of 'Sheet Name' => 'Data Array'
     * @param string $filename The name of the Excel file.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function exportMultipleSheets(array $sheets, string $filename = 'export.xlsx')
    {
        if (empty($sheets)) {
            return response()->json(['message' => 'No data available to export'], 400);
        }

        return Excel::download(new MultiSheetExport($sheets), $filename);
    }
}

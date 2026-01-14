<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class StreetlightPoleImportFormatExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * Return empty collection (template only, no data)
     */
    public function collection()
    {
        return new Collection([]);
    }

    /**
     * Get headings for streetlight pole import format
     */
    public function headings(): array
    {
        return [
            'district',
            'block',
            'panchayat',
            'ward_name',
            'complete_pole_number',
            'battery_qr',
            'luminary_qr',
            'panel_qr',
            'sim_number',
            'beneficiary',
            'beneficiary_contact',
            'lat',
            'long',
            'date_of_installation',
        ];
    }
}


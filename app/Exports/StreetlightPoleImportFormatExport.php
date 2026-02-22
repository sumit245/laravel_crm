<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

/**
 * Generates a blank Excel template for pole data import. Provides pre-formatted headers
 * (District, Block, Panchayat, Ward, Pole Number, Latitude, Longitude) for bulk pole data entry.
 *
 * Data Flow:
 *   User requests template → Generate Excel with headers + format guidelines → Download
 *   → Fill → Upload back
 *
 * @business-domain Field Operations
 * @package App\Exports
 */
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


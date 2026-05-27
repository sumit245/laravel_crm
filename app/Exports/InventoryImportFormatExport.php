<?php

namespace App\Exports;

use App\Support\StreetlightInventoryItems;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

/**
 * Generates a blank Excel template for inventory GRN import. Provides pre-formatted headers (Item
 * Code, Serial Number, SIM Number, Make, Model, Rate) so users can fill in data correctly.
 *
 * Data Flow:
 *   User requests template → Generate Excel with headers + sample data + validation notes
 *   → Download
 *
 * @business-domain Inventory & Warehouse
 * @package App\Exports
 */
class InventoryImportFormatExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $projectType;

    /**
     * Create a new InventoryImportFormatExport instance.
     *
     * @param  int  $projectType  
     */
    public function __construct(int $projectType)
    {
        $this->projectType = $projectType;
    }

    /**
     * Return sample rows so users can import without guessing valid codes.
     */
    public function collection()
    {
        if ($this->projectType == 1) {
            return new Collection(StreetlightInventoryItems::defaultTemplateRows());
        }

        return new Collection([
            ['Solar Panel 540W', 'Module', 'Panel', 'Nos', 1, 10000, 10000],
        ]);
    }

    /**
     * Get headings based on project type
     */
    public function headings(): array
    {
        if ($this->projectType == 1) {
            // Streetlight project format
            return [
                'item_code',
                'item',
                'manufacturer',
                'make',
                'model',
                'serial_number',
                'sim_number', // Only for luminary items
                'hsn',
                'unit',
                'unit_rate',
                'quantity',
                'total_value',
                'description',
                'e-way_bill',
                'received_date',
            ];
        } else {
            // Rooftop project format (based on InventoryImport)
            return [
                'item_description',
                'category',
                'sub_category',
                'unit',
                'quantity',
                'rate',
                'total',
            ];
        }
    }
}

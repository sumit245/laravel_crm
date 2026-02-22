<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Excel exporter for inventory data with dispatch status. Exports item code, serial number, SIM,
 * availability status, vendor name, and dates. Uses streaming for large datasets.
 *
 * Data Flow:
 *   Query builder with filters → Stream rows to Excel → Download file
 *
 * @depends-on Inventory, InventroyStreetLightModel, InventoryDispatch
 * @business-domain Inventory & Warehouse
 * @package App\Exports
 */
class InventoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    /**
     * Create a new InventoryExport instance.
     *
     * @param  mixed  $query  The search query string
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Build the query for export data.
     *
     * Data flow: Database Query → Collection → Excel Output
     *
     * @return void  
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Define the column headings for export.
     *
     * @return array  Result data array
     */
    public function headings(): array
    {
        return [
            'Item Code',
            'Item',
            'Serial Number',
            'SIM Number',
            'Availability',
            'Vendor',
            'Dispatch Date',
            'In Date',
        ];
    }

    /**
     * Map and transform the imported row data.
     *
     * @param  mixed  $item  The item model instance
     * @return array  Result data array
     */
    public function map($item): array
    {
        $availability = 'In Stock';
        if (!empty($item->streetlight_pole_id)) {
            $availability = 'Consumed';
        } elseif ($item->dispatch_id) {
            $availability = 'Dispatched';
        } elseif (($item->quantity ?? 0) > 0) {
            $availability = 'In Stock';
        }

        $vendorName = trim($item->vendor_name ?? '') ?: '-';
        $dispatchDate = $item->dispatch_date ? Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
        $receivedDate = $item->received_date
            ? Carbon::parse($item->received_date)->format('d/m/Y')
            : ($item->created_at ? Carbon::parse($item->created_at)->format('d/m/Y') : '-');

        $simNumber = (($item->item_code ?? '') === 'SL02' && trim((string) ($item->sim_number ?? '')) !== '')
            ? $item->sim_number
            : '-';

        return [
            $item->item_code,
            $item->item,
            $item->serial_number,
            $simNumber,
            $availability,
            $vendorName,
            $dispatchDate,
            $receivedDate,
        ];
    }
}

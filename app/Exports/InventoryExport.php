<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

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

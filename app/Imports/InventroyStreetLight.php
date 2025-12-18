<?php

namespace App\Imports;

use App\Models\InventroyStreetLightModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class InventroyStreetLight implements ToModel, WithHeadingRow
{
    protected $projectId, $storeId;

    // Constructor to accept project ID
    public function __construct($projectId, $storeId)
    {
        $this->projectId = $projectId;
        $this->storeId   = $storeId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validate item code for streetlight projects
        $validItemCodes = ['SL01', 'SL02', 'SL03', 'SL04'];
        if (!in_array($row['item_code'], $validItemCodes)) {
            throw new \Exception("Import failed: Invalid item code '{$row['item_code']}' for streetlight project. Allowed codes: SL01 (Panel), SL02 (Luminary), SL03 (Battery), SL04 (Structure).");
        }

        if ((int) $row['quantity'] === 0) {
            throw new \Exception("Import failed: Quantity cannot be zero for item code '{$row['item_code']}'");
        }
         // ✅ Check if serial number already exists in the database
        $existing = InventroyStreetLightModel::where('serial_number', $row['serial_number'])->exists();
        if ($existing) {
            throw new \Exception("Import failed: Duplicate serial number '{$row['serial_number']}' found.");
        }

        // ✅ Check if sim_number already exists for luminary items (SL02) only
        if (isset($row['item_code']) && $row['item_code'] === 'SL02' && !empty($row['sim_number'])) {
            $existingSim = InventroyStreetLightModel::where('sim_number', $row['sim_number'])
                ->where('item_code', 'SL02')
                ->exists();
            if ($existingSim) {
                throw new \Exception("Import failed: Duplicate SIM number '{$row['sim_number']}' found for luminary item.");
            }
        }

        $inventoryData = [
            'project_id' => $this->projectId,
            'store_id'   => $this->storeId,
            'item_code'  => $row['item_code'], // Ensure the key matches the header
            'item'       => $row['item'],
            'manufacturer' => $row['manufacturer'],
            'make'       => $row['make'],
            'model'      => $row['model'],
            'serial_number' => $row['serial_number'],
            'hsn'        => $row['hsn'],
            'unit'       => $row['unit'],
            'rate'       => $row['unit_rate'],
            'quantity'   => $row['quantity'],
            'total_value' => $row['total_value'],
            'description' => $row['description'] ?? "N/A",
            'eway_bill'  => $row['e-way_bill'] ?? "N/A",
            'received_date' => $this->parseDate($row['received_date']), // Adjusted key
        ];

        // Add sim_number only for luminary items (SL02)
        if (isset($row['item_code']) && $row['item_code'] === 'SL02' && isset($row['sim_number'])) {
            $inventoryData['sim_number'] = $row['sim_number'];
        }

        return new InventroyStreetLightModel($inventoryData);
    }

    /**
     * Parse the date string into a Carbon instance.
     *
     * @param string|null $dateString
     * @return \Carbon\Carbon|null
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null; // Return null if the date string is empty
        }

        // Use Carbon to parse the date string in dd-mm-yyyy format
        return Carbon::parse($dateString)->format('Y-m-d');
    }
}

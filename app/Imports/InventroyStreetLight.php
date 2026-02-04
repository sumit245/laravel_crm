<?php

namespace App\Imports;

use App\Models\InventroyStreetLightModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Carbon\Carbon;

class InventroyStreetLight implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $projectId, $storeId;
    /**
     * Track serials and SIM numbers seen during this import run
     * so duplicates within the same file can be skipped.
     */
    protected array $seenSerials = [];
    protected array $seenSimNumbers = [];
    protected array $errors = [];
    protected int $importedCount = 0;

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
        // Trim all scalar values to reduce formatting noise
        $row = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        // Strip completely empty or obviously incomplete rows
        $itemCodeRaw = $row['item_code'] ?? null;
        $serialRaw   = $row['serial_number'] ?? null;
        $itemName    = $row['item'] ?? null;

        if (
            (empty($itemCodeRaw) && empty($serialRaw) && empty($itemName)) ||
            (empty($serialRaw) || empty($itemName))
        ) {
            // Skip empty / malformed rows
            $this->errors[] = [
                'reason' => 'incomplete_row',
                'item_code' => $itemCodeRaw,
                'serial_number' => $serialRaw,
                'item' => $itemName,
            ];
            return null;
        }

        // Normalise item code (case-insensitive, tolerate missing zero: SL1 -> SL01)
        $itemCodeNormalized = null;
        if (!empty($itemCodeRaw)) {
            $upper = strtoupper($itemCodeRaw);
            // Match SL01, sl01, SL1, sl1 etc.
            if (preg_match('/^SL0?([1-4])$/i', $upper, $m)) {
                $itemCodeNormalized = 'SL0' . $m[1];
            } else {
                $itemCodeNormalized = $upper;
            }
        }

        $validItemCodes = ['SL01', 'SL02', 'SL03', 'SL04'];
        if (!in_array($itemCodeNormalized, $validItemCodes)) {
            // Invalid item code: skip this row
            $this->errors[] = [
                'reason' => 'invalid_item_code',
                'item_code' => $itemCodeRaw,
                'serial_number' => $serialRaw,
                'item' => $itemName,
            ];
            return null;
        }

        // Quantity rules:
        // - Quantity cannot be zero
        // - Any value > 1 is automatically converted to 1
        $quantityRaw = $row['quantity'] ?? 1;
        $quantity = (int) $quantityRaw;
        if ($quantity === 0) {
            // Business rule: zero quantity is not meaningful -> skip this row
            $this->errors[] = [
                'reason' => 'zero_quantity',
                'item_code' => $itemCodeNormalized,
                'serial_number' => $serialRaw,
                'item' => $itemName,
            ];
            return null;
        }
        if ($quantity > 1) {
            $quantity = 1;
        }

        $serial = $serialRaw;

        // Skip duplicates within the same file
        if ($serial && in_array($serial, $this->seenSerials, true)) {
            $this->errors[] = [
                'reason' => 'duplicate_serial_in_file',
                'item_code' => $itemCodeNormalized,
                'serial_number' => $serial,
                'item' => $itemName,
            ];
            return null;
        }

        // Skip if serial already exists in database
        if ($serial && InventroyStreetLightModel::where('serial_number', $serial)->exists()) {
            $this->errors[] = [
                'reason' => 'duplicate_serial_in_db',
                'item_code' => $itemCodeNormalized,
                'serial_number' => $serial,
                'item' => $itemName,
            ];
            return null;
        }

        if ($serial) {
            $this->seenSerials[] = $serial;
        }

        // Handle SIM number for luminary items (SL02)
        $sim = $row['sim_number'] ?? $row['SIM_NUMBER'] ?? null;
        if ($itemCodeNormalized === 'SL02' && !empty($sim)) {
            // Skip duplicates within the same file
            if (in_array($sim, $this->seenSimNumbers, true)) {
                $this->errors[] = [
                    'reason' => 'duplicate_sim_in_file',
                    'item_code' => $itemCodeNormalized,
                    'serial_number' => $serial,
                    'item' => $itemName,
                    'sim_number' => $sim,
                ];
                return null;
            }

            // Skip if SIM already exists for luminary items in DB
            $existingSim = InventroyStreetLightModel::where('sim_number', $sim)
                ->where('item_code', 'SL02')
                ->exists();
            if ($existingSim) {
                $this->errors[] = [
                    'reason' => 'duplicate_sim_in_db',
                    'item_code' => $itemCodeNormalized,
                    'serial_number' => $serial,
                    'item' => $itemName,
                    'sim_number' => $sim,
                ];
                return null;
            }

            $this->seenSimNumbers[] = $sim;
        }

        $rate = (float) ($row['unit_rate'] ?? 0);
        // Total value = rate * quantity (quantity always 1 by rule above)
        $totalValue = $rate * $quantity;

        $inventoryData = [
            'project_id'    => $this->projectId,
            'store_id'      => $this->storeId,
            'item_code'     => $itemCodeNormalized,
            'item'          => $itemName,
            'manufacturer'  => $row['manufacturer'] ?? null,
            'make'          => $row['make'] ?? null,
            'model'         => $row['model'] ?? null,
            'serial_number' => $serial,
            'hsn'           => $row['hsn'] ?? null,
            'unit'          => $row['unit'] ?? null,
            'rate'          => $rate,
            'quantity'      => $quantity,
            'total_value'   => $totalValue,
            'description'   => $row['description'] ?? 'N/A',
            'eway_bill'     => $row['e-way_bill'] ?? 'N/A',
            'received_date' => $this->parseDate($row['received_date'] ?? null),
        ];

        // Add sim_number only for luminary items (SL02)
        if ($itemCodeNormalized === 'SL02' && $sim) {
            $inventoryData['sim_number'] = $sim;
        }

        $this->importedCount++;
        return new InventroyStreetLightModel($inventoryData);
    }

    /**
     * Get collected error rows for reporting.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get count of successfully imported rows.
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Parse the date string into a Carbon instance.
     * Handles Excel date serial numbers and various date formats.
     *
     * @param string|null|int|float $dateString
     * @return string|null Formatted date as Y-m-d or null
     */
    private function parseDate($dateString)
    {
        if (empty($dateString) && $dateString !== 0 && $dateString !== '0') {
            return null;
        }

        try {
            // Handle Excel date serial numbers (numeric values)
            // Excel stores dates as numbers where 1 = January 1, 1900
            if (is_numeric($dateString)) {
                $serialNumber = (float) $dateString;
                // Excel epoch starts on January 1, 1900, but Excel incorrectly treats 1900 as a leap year
                // So we need to adjust: Excel date 1 = 1900-01-01, but we subtract 2 days to account for the bug
                $excelEpoch = Carbon::create(1899, 12, 30);
                $parsedDate = $excelEpoch->copy()->addDays($serialNumber);
                return $parsedDate->format('Y-m-d');
            }

            // Handle string dates - try multiple formats
            $dateString = trim((string) $dateString);
            
            // Try Carbon's intelligent parsing first
            try {
                $parsedDate = Carbon::parse($dateString);
                return $parsedDate->format('Y-m-d');
            } catch (\Exception $e) {
                // Try common date formats
                $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'm-d-Y', 'm/d/Y'];
                foreach ($formats as $format) {
                    try {
                        $parsedDate = Carbon::createFromFormat($format, $dateString);
                        return $parsedDate->format('Y-m-d');
                    } catch (\Exception $formatException) {
                        continue;
                    }
                }
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}

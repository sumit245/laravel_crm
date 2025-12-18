<?php

namespace App\Services\Inventory\Strategies;

use App\Contracts\InventoryStrategyInterface;
use App\Models\InventroyStreetLightModel;

/**
 * Streetlight Inventory Strategy
 * 
 * Handles inventory operations for streetlight projects
 */
class StreetlightInventoryStrategy implements InventoryStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getModelClass(): string
    {
        return InventroyStreetLightModel::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidationRules(array $data): array
    {
        // Accept form field names (code, dropdown, number, serialnumber, etc.)
        // The prepareForStorage method will map them to the correct database fields
        $itemCode = $data['code'] ?? $data['item_code'] ?? null;
        
        // Valid streetlight item codes
        $validItemCodes = ['SL01', 'SL02', 'SL03', 'SL04'];
        
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'store_id' => 'required|exists:stores,id',
            'code' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validItemCodes) {
                if (!in_array($value, $validItemCodes)) {
                    $fail('Invalid item code for streetlight project. Allowed codes: SL01 (Panel), SL02 (Luminary), SL03 (Battery), SL04 (Structure).');
                }
            }], // Form field: item_code
            'dropdown' => 'required|string|max:255', // Form field: item
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serialnumber' => 'required|string|max:255', // Form field: serial_number
            'make' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'number' => 'required|numeric|min:0', // Form field: quantity
            'totalvalue' => 'nullable|numeric|min:0', // Form field: total_value
            'hsncode' => 'required|string|max:50', // Form field: hsn
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'receiveddate' => 'required|date', // Form field: received_date
        ];

        // Add sim_number validation for luminary items (SL02) only
        // Note: Uniqueness will be checked in controller with custom rule to ensure it's only for SL02 items
        if ($itemCode === 'SL02') {
            $rules['sim_number'] = 'nullable|string|max:200';
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function calculateTotalValue(float $quantity, float $rate): float
    {
        return $quantity * $rate;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareForStorage(array $data): array
    {
        // Map form field names to expected database field names
        $mappedData = [
            'project_id' => $data['project_id'] ?? null,
            'store_id' => $data['store_id'] ?? null,
            'item_code' => $data['item_code'] ?? $data['code'] ?? null,
            'item' => $data['item'] ?? $data['dropdown'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? $data['serialnumber'] ?? null,
            'sim_number' => $data['sim_number'] ?? null, // Only for luminary items (SL02)
            'make' => $data['make'] ?? null,
            'rate' => $data['rate'] ?? null,
            'quantity' => $data['quantity'] ?? $data['number'] ?? 1,
            'hsn' => $data['hsn'] ?? $data['hsncode'] ?? null,
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? null,
            'received_date' => $data['received_date'] ?? $data['receiveddate'] ?? null,
        ];

        // Calculate total value if not provided
        $totalValue = $data['total_value'] ?? $data['totalvalue'] ?? null;
        if (!$totalValue && isset($mappedData['quantity'], $mappedData['rate'])) {
            $mappedData['total_value'] = $this->calculateTotalValue(
                (float) $mappedData['quantity'],
                (float) $mappedData['rate']
            );
        } else {
            $mappedData['total_value'] = $totalValue;
        }

        // Remove null values to avoid issues
        return array_filter($mappedData, function($value) {
            return $value !== null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableStock(int $projectId, int $storeId, string $itemCode): float
    {
        return InventroyStreetLightModel::where('project_id', $projectId)
            ->where('store_id', $storeId)
            ->where('item_code', $itemCode)
            ->sum('quantity');
    }
}

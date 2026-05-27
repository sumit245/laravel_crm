<?php

namespace App\Services\Inventory\Strategies;

use App\Contracts\InventoryStrategyInterface;
use App\Models\InventroyStreetLightModel;
use App\Support\StreetlightInventoryItems;

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
        
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'store_id' => 'required|exists:stores,id',
            'code' => 'required|string|max:255', // Form field: item_code
            'dropdown' => 'required|string|max:255', // Form field: item
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serialnumber' => 'required|string|max:255', // Form field: serial_number - uniqueness checked in controller
            'make' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric|min:0',
            'number' => 'required|numeric|min:0', // Form field: quantity (always 1 for single item)
            'totalvalue' => 'nullable|numeric|min:0', // Form field: total_value
            'hsncode' => 'nullable|string|max:50', // Form field: hsn
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'receiveddate' => 'nullable|date', // Form field: received_date
        ];

        // Add sim_number validation for luminary items only.
        if (StreetlightInventoryItems::isLuminary($data['dropdown'] ?? $data['item'] ?? null, $itemCode)) {
            $rules['sim_number'] = 'required|string|max:200';
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
            'item_code' => StreetlightInventoryItems::normalizeCode($data['item_code'] ?? $data['code'] ?? null),
            'item' => $data['item'] ?? $data['dropdown'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? $data['serialnumber'] ?? null,
            'sim_number' => StreetlightInventoryItems::isLuminary(
                $data['item'] ?? $data['dropdown'] ?? null,
                $data['item_code'] ?? $data['code'] ?? null
            ) ? ($data['sim_number'] ?? null) : null,
            'make' => $data['make'] ?? 'Sugs',
            'rate' => $data['rate'] ?? 100,
            'quantity' => $data['quantity'] ?? $data['number'] ?? 1,
            'hsn' => $data['hsn'] ?? $data['hsncode'] ?? '123456',
            'description' => $data['description'] ?? '',
            'unit' => $data['unit'] ?? 'PCS',
            'received_date' => $data['received_date'] ?? $data['receiveddate'] ?? date('Y-m-d'),
        ];

        // Calculate total value if not provided
        $totalValue = $data['total_value'] ?? $data['totalvalue'] ?? null;
        if (!$totalValue && isset($mappedData['quantity'], $mappedData['rate'])) {
            $mappedData['total_value'] = $this->calculateTotalValue(
                (float) $mappedData['quantity'],
                (float) $mappedData['rate']
            );
        } else {
            $mappedData['total_value'] = $totalValue ?? ($mappedData['rate'] * $mappedData['quantity']);
        }

        // Remove null values to avoid issues, but keep empty strings
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

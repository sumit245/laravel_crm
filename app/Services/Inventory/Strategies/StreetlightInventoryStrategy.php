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
        return [
            'project_id' => 'required|exists:projects,id',
            'store_id' => 'required|exists:stores,id',
            'item' => 'required|string|max:255',
            'item_code' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'hsn' => 'required|string|max:50',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'received_date' => 'required|date',
        ];
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
        // Calculate total value if not provided
        if (!isset($data['total_value']) && isset($data['quantity'], $data['rate'])) {
            $data['total_value'] = $this->calculateTotalValue(
                (float) $data['quantity'],
                (float) $data['rate']
            );
        }

        return $data;
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

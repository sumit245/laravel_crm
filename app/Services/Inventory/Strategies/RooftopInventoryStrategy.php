<?php

namespace App\Services\Inventory\Strategies;

use App\Contracts\InventoryStrategyInterface;
use App\Models\Inventory;

/**
 * Rooftop Inventory Strategy
 * 
 * Handles inventory operations for rooftop solar projects
 */
class RooftopInventoryStrategy implements InventoryStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getModelClass(): string
    {
        return Inventory::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidationRules(array $data): array
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'project_id' => 'required|exists:projects,id',
            'site_id' => 'nullable|exists:sites,id',
            'category' => 'nullable|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'productName' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'initialQuantity' => 'required|numeric|min:0',
            'quantityStock' => 'nullable|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'materialDispatchDate' => 'nullable|date',
            'deliveryDate' => 'nullable|date',
            'receivedDate' => 'nullable|date',
            'allocationOfficer' => 'nullable|string|max:255',
            'url' => 'nullable|url',
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
        // Set default values
        if (!isset($data['quantityStock'])) {
            $data['quantityStock'] = $data['initialQuantity'] ?? 0;
        }

        // Calculate total if not provided
        if (!isset($data['total']) && isset($data['initialQuantity'], $data['rate'])) {
            $data['total'] = $this->calculateTotalValue(
                (float) $data['initialQuantity'],
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
        return Inventory::where('project_id', $projectId)
            ->where('store_id', $storeId)
            ->where('productName', $itemCode)
            ->sum('quantityStock');
    }
}

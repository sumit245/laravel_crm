<?php

namespace App\Contracts;

/**
 * Strategy pattern interface for inventory operations. Different project types (streetlight vs
 * rooftop) use different inventory models and logic. This interface ensures consistent API across
 * strategies.
 *
 * Data Flow:
 *   InventoryController → Resolve strategy by project type → Execute through uniform
 *   interface → Model-specific logic runs
 *
 * @business-domain Inventory & Warehouse
 * @package App\Contracts
 */
interface InventoryStrategyInterface
{
    /**
     * Get the inventory model class name
     *
     * @return string
     */
    public function getModelClass(): string;

    /**
     * Validate inventory data
     *
     * @param array $data
     * @return array Validation rules
     */
    public function getValidationRules(array $data): array;

    /**
     * Calculate total value
     *
     * @param float $quantity
     * @param float $rate
     * @return float
     */
    public function calculateTotalValue(float $quantity, float $rate): float;

    /**
     * Prepare data for storage
     *
     * @param array $data
     * @return array
     */
    public function prepareForStorage(array $data): array;

    /**
     * Get available stock for an item
     *
     * @param int $projectId
     * @param int $storeId
     * @param string $itemCode
     * @return float
     */
    public function getAvailableStock(int $projectId, int $storeId, string $itemCode): float;
}

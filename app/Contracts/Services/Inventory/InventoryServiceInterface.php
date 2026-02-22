<?php

namespace App\Contracts\Services\Inventory;

/**
 * Contract for inventory service operations. Defines methods for adding, dispatching, returning,
 * and replacing inventory items with proper validation.
 *
 * Data Flow:
 *   InventoryController → InventoryService (implements this) → Validate + execute →
 *   Model operations → History logging
 *
 * @business-domain Inventory & Warehouse
 * @package App\Contracts\Services\Inventory
 */
interface InventoryServiceInterface
{
    /**
     * Set the inventory strategy based on project type
     */
    public function setStrategy(int $projectType): self;

    /**
     * Add inventory item
     */
    public function addInventoryItem(array $data, int $projectType): mixed;

    /**
     * Update inventory quantity
     */
    public function updateInventoryQuantity(int $inventoryId, float $quantity, int $projectType): bool;

    /**
     * Get available stock for an item
     */
    public function getMaterialAvailability(int $projectId, int $storeId, string $itemCode, int $projectType): float;

    /**
     * Dispatch material
     */
    public function dispatchMaterial(array $dispatchData): mixed;
}

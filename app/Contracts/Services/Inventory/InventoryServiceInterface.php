<?php

namespace App\Contracts\Services\Inventory;

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

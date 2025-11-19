<?php

namespace App\Services\Inventory;

use App\Contracts\InventoryStrategyInterface;
use App\Contracts\Services\Inventory\InventoryServiceInterface;
use App\Enums\ProjectType;
use App\Services\BaseService;
use App\Services\Inventory\Strategies\RooftopInventoryStrategy;
use App\Services\Inventory\Strategies\StreetlightInventoryStrategy;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Inventory Management Service
 * 
 * Handles inventory operations using strategy pattern for different project types
 */
class InventoryService extends BaseService implements InventoryServiceInterface
{
    protected InventoryStrategyInterface $strategy;

    /**
     * Set the inventory strategy based on project type
     *
     * @param int $projectType
     * @return self
     */
    public function setStrategy(int $projectType): self
    {
        $type = ProjectType::fromValue($projectType);

        $this->strategy = match($type) {
            ProjectType::ROOFTOP_SOLAR => new RooftopInventoryStrategy(),
            ProjectType::STREETLIGHT => new StreetlightInventoryStrategy(),
        };

        return $this;
    }

    /**
     * Add inventory item
     *
     * @param array $data
     * @param int $projectType
     * @return mixed
     */
    public function addInventoryItem(array $data, int $projectType): mixed
    {
        return $this->executeInTransaction(function () use ($data, $projectType) {
            // Set strategy based on project type
            $this->setStrategy($projectType);

            // Validate data
            $this->validateInventoryData($data);

            // Prepare data for storage
            $preparedData = $this->strategy->prepareForStorage($data);

            // Get model class
            $modelClass = $this->strategy->getModelClass();

            // Create inventory record
            $inventory = $modelClass::create($preparedData);

            $this->logInfo('Inventory item added', [
                'project_type' => $projectType,
                'item_code' => $preparedData['item_code'] ?? $preparedData['productName'] ?? 'N/A'
            ]);

            return $inventory;
        });
    }

    /**
     * Update inventory quantity
     *
     * @param int $inventoryId
     * @param float $quantity
     * @param int $projectType
     * @return bool
     */
    public function updateInventoryQuantity(int $inventoryId, float $quantity, int $projectType): bool
    {
        return $this->executeInTransaction(function () use ($inventoryId, $quantity, $projectType) {
            $this->setStrategy($projectType);

            $modelClass = $this->strategy->getModelClass();
            $inventory = $modelClass::find($inventoryId);

            if (!$inventory) {
                throw new \Exception('Inventory item not found');
            }

            // Update quantity based on model type
            if ($projectType == ProjectType::ROOFTOP_SOLAR->value) {
                $inventory->quantityStock = $quantity;
            } else {
                $inventory->quantity = $quantity;
            }

            $result = $inventory->save();

            if ($result) {
                $this->logInfo('Inventory quantity updated', [
                    'inventory_id' => $inventoryId,
                    'new_quantity' => $quantity
                ]);
            }

            return $result;
        });
    }

    /**
     * Get available stock for an item
     *
     * @param int $projectId
     * @param int $storeId
     * @param string $itemCode
     * @param int $projectType
     * @return float
     */
    public function getMaterialAvailability(int $projectId, int $storeId, string $itemCode, int $projectType): float
    {
        $this->setStrategy($projectType);
        return $this->strategy->getAvailableStock($projectId, $storeId, $itemCode);
    }

    /**
     * Dispatch material
     *
     * @param array $dispatchData
     * @return mixed
     */
    public function dispatchMaterial(array $dispatchData): mixed
    {
        return $this->executeInTransaction(function () use ($dispatchData) {
            // Validate dispatch data
            $this->validateDispatchData($dispatchData);

            // Check if sufficient stock available
            $available = $this->getMaterialAvailability(
                $dispatchData['project_id'],
                $dispatchData['store_id'],
                $dispatchData['item_code'],
                $dispatchData['project_type']
            );

            if ($available < $dispatchData['total_quantity']) {
                throw new \Exception('Insufficient stock available for dispatch');
            }

            // Create dispatch record
            $dispatch = \App\Models\InventoryDispatch::create($dispatchData);

            // Update inventory quantity
            $this->reduceInventoryStock(
                $dispatchData['project_id'],
                $dispatchData['store_id'],
                $dispatchData['item_code'],
                $dispatchData['total_quantity'],
                $dispatchData['project_type']
            );

            $this->logInfo('Material dispatched', [
                'dispatch_id' => $dispatch->id,
                'item_code' => $dispatchData['item_code'],
                'quantity' => $dispatchData['total_quantity']
            ]);

            return $dispatch;
        });
    }

    /**
     * Reduce inventory stock after dispatch
     *
     * @param int $projectId
     * @param int $storeId
     * @param string $itemCode
     * @param float $quantity
     * @param int $projectType
     * @return void
     */
    protected function reduceInventoryStock(int $projectId, int $storeId, string $itemCode, float $quantity, int $projectType): void
    {
        $this->setStrategy($projectType);
        $modelClass = $this->strategy->getModelClass();

        if ($projectType == ProjectType::ROOFTOP_SOLAR->value) {
            $inventory = $modelClass::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->where('productName', $itemCode)
                ->first();

            if ($inventory) {
                $inventory->quantityStock -= $quantity;
                $inventory->save();
            }
        } else {
            // For streetlight, find matching item and reduce quantity
            $inventory = $modelClass::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->where('item_code', $itemCode)
                ->where('quantity', '>=', $quantity)
                ->first();

            if ($inventory) {
                $inventory->quantity -= $quantity;
                $inventory->save();
            }
        }
    }

    /**
     * Validate inventory data
     *
     * @param array $data
     * @throws ValidationException
     */
    protected function validateInventoryData(array $data): void
    {
        $rules = $this->strategy->getValidationRules($data);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate dispatch data
     *
     * @param array $data
     * @throws ValidationException
     */
    protected function validateDispatchData(array $data): void
    {
        $rules = [
            'vendor_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'store_id' => 'required|exists:stores,id',
            'store_incharge_id' => 'required|exists:users,id',
            'item_code' => 'required|string',
            'item' => 'required|string',
            'rate' => 'required|numeric|min:0',
            'total_quantity' => 'required|numeric|min:0.01',
            'total_value' => 'required|numeric|min:0',
            'project_type' => 'required|in:0,1',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}

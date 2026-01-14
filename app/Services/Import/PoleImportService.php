<?php

namespace App\Services\Import;

use App\Models\StreetlightTask;
use App\Models\InventroyStreetLightModel;
use App\Models\InventoryDispatch;
use App\Models\Stores;
use App\Services\BaseService;
use App\Services\Inventory\InventoryHistoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PoleImportService extends BaseService
{
    protected InventoryHistoryService $historyService;

    public function __construct(InventoryHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Validate and auto-dispatch inventory item if needed
     * 
     * @param string $serialNumber
     * @param StreetlightTask $task
     * @return array Returns ['status' => 'valid'|'error', 'dispatch' => InventoryDispatch|null, 'error' => string|null]
     */
    public function validateAndDispatchInventory(string $serialNumber, StreetlightTask $task): array
    {
        try {
            // Check if item exists in inventory
            $inventoryItem = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();

            if (!$inventoryItem) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => "Item with serial number '{$serialNumber}' not found in inventory"
                ];
            }

            // Check if item has quantity > 0 (in stock)
            if ($inventoryItem->quantity > 0) {
                // Check if dispatch already exists
                $existingDispatch = InventoryDispatch::where('serial_number', $serialNumber)
                    ->where('isDispatched', true)
                    ->first();

                if ($existingDispatch) {
                    // Dispatch exists, check if consumed
                    if ($existingDispatch->is_consumed) {
                        return [
                            'status' => 'error',
                            'dispatch' => null,
                            'error' => "Item with serial number '{$serialNumber}' is already consumed"
                        ];
                    }
                    // Valid - dispatch exists and not consumed
                    return [
                        'status' => 'valid',
                        'dispatch' => $existingDispatch,
                        'error' => null
                    ];
                }

                // Item is in stock but not dispatched - auto-dispatch
                return $this->autoDispatchInventory($serialNumber, $inventoryItem, $task);
            } else {
                // Quantity is 0, check dispatch status
                $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                    ->where('isDispatched', true)
                    ->first();

                if (!$dispatch) {
                    return [
                        'status' => 'error',
                        'dispatch' => null,
                        'error' => "Item with serial number '{$serialNumber}' is out of stock and not dispatched to vendor"
                    ];
                }

                if ($dispatch->is_consumed) {
                    return [
                        'status' => 'error',
                        'dispatch' => null,
                        'error' => "Item with serial number '{$serialNumber}' is already consumed"
                    ];
                }

                // Valid - dispatch exists and not consumed (in vendor custody)
                return [
                    'status' => 'valid',
                    'dispatch' => $dispatch,
                    'error' => null
                ];
            }
        } catch (\Exception $e) {
            $this->logError('Error validating inventory', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'dispatch' => null,
                'error' => "Error validating inventory: " . $e->getMessage()
            ];
        }
    }

    /**
     * Auto-dispatch inventory item to vendor
     * 
     * @param string $serialNumber
     * @param InventroyStreetLightModel $inventoryItem
     * @param StreetlightTask $task
     * @return array
     */
    protected function autoDispatchInventory(string $serialNumber, InventroyStreetLightModel $inventoryItem, StreetlightTask $task): array
    {
        return $this->executeInTransaction(function () use ($serialNumber, $inventoryItem, $task) {
            // Get vendor_id from task
            if (!$task->vendor_id) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => "Task does not have a vendor assigned. Cannot auto-dispatch inventory."
                ];
            }

            // Get store_id from inventory item
            $storeId = $inventoryItem->store_id;
            if (!$storeId) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => "Inventory item does not have a store assigned. Cannot auto-dispatch."
                ];
            }

            // Get store_incharge_id from store
            $store = Stores::find($storeId);
            if (!$store || !$store->store_incharge_id) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => "Store does not have an incharge assigned. Cannot auto-dispatch."
                ];
            }

            // Get project_id from task
            $projectId = $task->project_id;

            // Create dispatch record
            $dispatch = InventoryDispatch::create([
                'vendor_id' => $task->vendor_id,
                'project_id' => $projectId,
                'store_id' => $storeId,
                'store_incharge_id' => $store->store_incharge_id,
                'item_code' => $inventoryItem->item_code,
                'item' => $inventoryItem->item,
                'rate' => $inventoryItem->rate,
                'make' => $inventoryItem->make,
                'model' => $inventoryItem->model,
                'total_quantity' => 1,
                'total_value' => $inventoryItem->rate,
                'serial_number' => $serialNumber,
                'dispatch_date' => Carbon::now(),
                'isDispatched' => true,
                'is_consumed' => false,
            ]);

            // Decrement inventory quantity
            $inventoryItem->decrement('quantity', 1);

            // Log dispatch history
            $project = $task->project;
            $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
            $this->historyService->logDispatched($dispatch, $inventoryItem, $inventoryType);

            $this->logInfo('Auto-dispatched inventory item', [
                'serial_number' => $serialNumber,
                'vendor_id' => $task->vendor_id,
                'dispatch_id' => $dispatch->id
            ]);

            return [
                'status' => 'valid',
                'dispatch' => $dispatch,
                'error' => null
            ];
        });
    }

    /**
     * Consume inventory for pole (mark dispatch as consumed and link to pole)
     * 
     * @param \App\Models\Pole $pole
     * @param array $serialNumbers Array of serial numbers to consume
     * @return void
     */
    public function consumeInventoryForPole($pole, array $serialNumbers): void
    {
        $this->executeInTransaction(function () use ($pole, $serialNumbers) {
            foreach ($serialNumbers as $serialNumber) {
                if (empty($serialNumber)) {
                    continue;
                }

                $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                    ->whereNull('streetlight_pole_id')
                    ->where('is_consumed', false)
                    ->where('isDispatched', true)
                    ->first();

                if ($dispatch) {
                    $dispatch->update([
                        'streetlight_pole_id' => $pole->id,
                        'is_consumed' => true,
                        'updated_at' => Carbon::now()
                    ]);

                    // Log consumption
                    $project = $pole->task->project ?? null;
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logConsumed($dispatch, $inventoryType, $pole);
                }
            }
        });
    }
}


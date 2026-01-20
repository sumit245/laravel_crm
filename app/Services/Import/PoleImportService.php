<?php

namespace App\Services\Import;

use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Stores;
use App\Models\StreetlightTask;
use App\Services\BaseService;
use App\Services\Inventory\InventoryHistoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
     * @return array Returns ['status' => 'valid'|'error', 'dispatch' => InventoryDispatch|null, 'error' => string|null]
     */
    public function validateAndDispatchInventory(string $serialNumber, StreetlightTask $task): array
    {
        try {
            // Check if item exists in inventory
            $inventoryItem = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();

            if (! $inventoryItem) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => "Item with serial number '{$serialNumber}' not found in inventory",
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
                            'error' => "Item with serial number '{$serialNumber}' is already consumed",
                        ];
                    }

                    // Valid - dispatch exists and not consumed
                    return [
                        'status' => 'valid',
                        'dispatch' => $existingDispatch,
                        'error' => null,
                    ];
                }

                // Item is in stock but not dispatched - auto-dispatch
                return $this->autoDispatchInventory($serialNumber, $inventoryItem, $task);
            } else {
                // Quantity is 0, check dispatch status
                $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                    ->where('isDispatched', true)
                    ->first();

                if (! $dispatch) {
                    return [
                        'status' => 'error',
                        'dispatch' => null,
                        'error' => "Item with serial number '{$serialNumber}' is out of stock and not dispatched to vendor",
                    ];
                }

                if ($dispatch->is_consumed) {
                    return [
                        'status' => 'error',
                        'dispatch' => null,
                        'error' => "Item with serial number '{$serialNumber}' is already consumed",
                    ];
                }

                // Valid - dispatch exists and not consumed (in vendor custody)
                return [
                    'status' => 'valid',
                    'dispatch' => $dispatch,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            $this->logError('Error validating inventory', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'dispatch' => null,
                'error' => 'Error validating inventory: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Auto-dispatch inventory item to vendor
     */
    protected function autoDispatchInventory(string $serialNumber, InventroyStreetLightModel $inventoryItem, StreetlightTask $task): array
    {
        return $this->executeInTransaction(function () use ($serialNumber, $inventoryItem, $task) {
            // Get vendor_id from task
            if (! $task->vendor_id) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => 'Task does not have a vendor assigned. Cannot auto-dispatch inventory.',
                ];
            }

            // Get store_id from inventory item
            $storeId = $inventoryItem->store_id;
            if (! $storeId) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => 'Inventory item does not have a store assigned. Cannot auto-dispatch.',
                ];
            }

            // Get store_incharge_id from store
            $store = Stores::find($storeId);
            if (! $store || ! $store->store_incharge_id) {
                return [
                    'status' => 'error',
                    'dispatch' => null,
                    'error' => 'Store does not have an incharge assigned. Cannot auto-dispatch.',
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
                'dispatch_id' => $dispatch->id,
            ]);

            return [
                'status' => 'valid',
                'dispatch' => $dispatch,
                'error' => null,
            ];
        });
    }

    /**
     * Consume inventory for pole (mark dispatch as consumed and link to pole)
     *
     * @param  \App\Models\Pole  $pole
     * @param  array  $serialNumbers  Array of serial numbers to consume
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
                        'updated_at' => Carbon::now(),
                    ]);

                    // Log consumption
                    $project = $pole->task->project ?? null;
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logConsumed($dispatch, $inventoryType, $pole);
                }
            }
        });
    }

    // ... existing PoleImportService code ...

    public function updateExistingPoleWithInventory($pole, array $row, $task): array
    {
        // Build new desired items from row
        $newBatteryQr = trim($row['battery_qr'] ?? '');
        $newLuminaryQr = trim($row['luminary_qr'] ?? '');
        $newPanelQr = trim($row['panel_qr'] ?? '');

        $itemsToValidate = [];
        if ($newBatteryQr) {
            $itemsToValidate['battery_qr'] = $newBatteryQr;
        }
        if ($newLuminaryQr) {
            $itemsToValidate['luminary_qr'] = $newLuminaryQr;
        }
        if ($newPanelQr) {
            $itemsToValidate['panel_qr'] = $newPanelQr;
        }

        $validationErrors = [];
        $validDispatches = [];

        // 1. Validate all new items using existing logic
        foreach ($itemsToValidate as $field => $serialNumber) {
            $validation = $this->validateAndDispatchInventory($serialNumber, $task);

            if ($validation['status'] === 'error') {
                $validationErrors[] = $validation['error'];
            } else {
                $validDispatches[$field] = $validation['dispatch'];
            }
        }

        if (! empty($validationErrors)) {
            return [
                'status' => 'error',
                'error' => implode('; ', $validationErrors),
            ];
        }

        // 2. Constraint (a): all items must belong to same store and same vendor, and not consumed
        $storeIds = [];
        $vendorIds = [];

        foreach ($validDispatches as $dispatch) {
            // dispatches here are already "not consumed" by validateAndDispatchInventory
            $storeIds[] = $dispatch->store_id;
            $vendorIds[] = $dispatch->vendor_id;
        }

        $storeIds = array_filter(array_unique($storeIds));
        $vendorIds = array_filter(array_unique($vendorIds));

        if (count($storeIds) > 1 || count($vendorIds) > 1) {
            return [
                'status' => 'error',
                'error' => 'All inventory items must belong to the same store and be dispatched to the same vendor',
            ];
        }

        // 3. Start transaction to handle replacement and pole update
        return $this->executeInTransaction(function () use ($pole, $row, $task, $itemsToValidate, $validDispatches) {
            $replacedItems = [];

            // For each component, if serial changes, "return" old consumed item and consume new one
            foreach (['battery_qr', 'luminary_qr', 'panel_qr'] as $field) {
                $newSerial = $itemsToValidate[$field] ?? null;
                $newDisp = $validDispatches[$field] ?? null;
                $oldSerial = $pole->{$field} ?? null;

                // No change for this field
                if (! $newSerial || $newSerial === $oldSerial) {
                    continue;
                }

                // 3.a Find existing consumed dispatch for old serial (if any)
                if ($oldSerial) {
                    $oldDispatch = \App\Models\InventoryDispatch::where('serial_number', $oldSerial)
                        ->where('streetlight_pole_id', $pole->id)
                        ->where('is_consumed', true)
                        ->where('isDispatched', true)
                        ->first();

                    if ($oldDispatch) {
                        // Constraint (b): old and new must be same store and same vendor
                        if ($newDisp &&
                            ($oldDispatch->store_id != $newDisp->store_id ||
                             $oldDispatch->vendor_id != $newDisp->vendor_id)) {
                            return [
                                'status' => 'error',
                                'error' => "Existing and replacement items for {$field} must be from the same store and vendor",
                            ];
                        }

                        // Return old item to inventory
                        $oldInventory = $oldDispatch->inventoryStreetLight;
                        if ($oldInventory) {
                            $oldInventory->increment('quantity', 1);

                            $project = $task->project;
                            $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';

                            $this->historyService->logReturned(
                                $oldInventory,
                                $inventoryType,
                                $project->id ?? 0,
                                $oldInventory->store_id,
                                1
                            );
                        }

                        // Mark dispatch as not consumed and detach from pole
                        $oldDispatch->update([
                            'is_consumed' => false,
                            'streetlight_pole_id' => null,
                        ]);
                    }
                }

                // 3.b Consume new (validated) dispatch for this pole
                if ($newDisp) {
                    $newDisp->update([
                        'streetlight_pole_id' => $pole->id,
                        'is_consumed' => true,
                    ]);

                    $project = $task->project;
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';

                    $this->historyService->logConsumed($newDisp, $inventoryType, $pole);
                }

                // Update pole field to new serial
                $pole->{$field} = $newSerial;
                $replacedItems[] = $field;
            }

            // 4. Update other pole attributes from row (beneficiary, ward, etc.)
            $pole->beneficiary = ! empty($row['beneficiary']) ? trim($row['beneficiary']) : $pole->beneficiary;
            $pole->beneficiary_contact = ! empty($row['beneficiary_contact']) ? trim($row['beneficiary_contact']) : $pole->beneficiary_contact;
            $pole->ward_name = ! empty($row['ward_name']) ? trim($row['ward_name']) : $pole->ward_name;
            $pole->sim_number = ! empty($row['sim_number']) ? trim($row['sim_number']) : $pole->sim_number;
            $pole->lat = ! empty($row['lat']) ? $row['lat'] : $pole->lat;
            $pole->lng = ! empty($row['long']) ? $row['long'] : $pole->lng;

            // Do not change survey/installation flags here; this is strictly a correction/replacement
            $pole->save();

            return [
                'status' => 'success',
                'replaced_items' => $replacedItems,
            ];
        });
    }
}

<?php

namespace App\Services\Inventory;

use App\Models\InventoryHistory;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class InventoryHistoryService extends BaseService
{
    /**
     * Log inventory operation
     *
     * @param string $action
     * @param array $data
     * @return InventoryHistory
     */
    public function log(string $action, array $data): InventoryHistory
    {
        return InventoryHistory::create([
            'inventory_id' => $data['inventory_id'] ?? null,
            'inventory_type' => $data['inventory_type'] ?? null,
            'action' => $action,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'project_id' => $data['project_id'] ?? null,
            'store_id' => $data['store_id'] ?? null,
            'quantity_before' => $data['quantity_before'] ?? null,
            'quantity_after' => $data['quantity_after'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Log inventory creation
     */
    public function logCreated($inventory, string $inventoryType, int $projectId, int $storeId): InventoryHistory
    {
        return $this->log('created', [
            'inventory_id' => $inventory->id,
            'inventory_type' => $inventoryType,
            'project_id' => $projectId,
            'store_id' => $storeId,
            'quantity_before' => 0,
            'quantity_after' => $inventory->quantity ?? 1,
            'serial_number' => $inventory->serial_number ?? null,
        ]);
    }

    /**
     * Log inventory dispatch
     */
    public function logDispatched($dispatch, $inventory, string $inventoryType): InventoryHistory
    {
        return $this->log('dispatched', [
            'inventory_id' => $inventory->id ?? null,
            'inventory_type' => $inventoryType,
            'project_id' => $dispatch->project_id,
            'store_id' => $dispatch->store_id,
            'quantity_before' => ($inventory->quantity ?? 0) + ($dispatch->total_quantity ?? 1),
            'quantity_after' => $inventory->quantity ?? 0,
            'serial_number' => $dispatch->serial_number ?? null,
            'metadata' => [
                'vendor_id' => $dispatch->vendor_id,
                'dispatch_id' => $dispatch->id,
            ],
        ]);
    }

    /**
     * Log inventory return
     */
    public function logReturned($inventory, string $inventoryType, int $projectId, int $storeId, int $quantityReturned): InventoryHistory
    {
        return $this->log('returned', [
            'inventory_id' => $inventory->id,
            'inventory_type' => $inventoryType,
            'project_id' => $projectId,
            'store_id' => $storeId,
            'quantity_before' => $inventory->quantity - $quantityReturned,
            'quantity_after' => $inventory->quantity,
            'serial_number' => $inventory->serial_number ?? null,
            'metadata' => [
                'quantity_returned' => $quantityReturned,
            ],
        ]);
    }

    /**
     * Log inventory replacement
     */
    public function logReplaced($oldInventory, $newInventory, string $inventoryType, int $projectId, int $storeId, $pole = null): InventoryHistory
    {
        $metadata = [
            'old_serial_number' => $oldInventory->serial_number ?? null,
            'new_serial_number' => $newInventory->serial_number ?? null,
        ];

        // Add pole information if available
        if ($pole) {
            $metadata['pole_id'] = $pole->id;
            $metadata['complete_pole_number'] = $pole->complete_pole_number ?? null;
            $metadata['site_id'] = $pole->task->site_id ?? null;
        }

        // Get previous replacement history to track all poles
        $previousReplacements = InventoryHistory::where('serial_number', $oldInventory->serial_number ?? null)
            ->where('action', 'replaced')
            ->get()
            ->map(function ($history) {
                return $history->metadata['complete_pole_number'] ?? null;
            })
            ->filter()
            ->toArray();

        if (!empty($previousReplacements)) {
            $metadata['replaced_from_poles'] = $previousReplacements;
        }

        return $this->log('replaced', [
            'inventory_id' => $newInventory->id ?? null,
            'inventory_type' => $inventoryType,
            'project_id' => $projectId,
            'store_id' => $storeId,
            'quantity_before' => $oldInventory->quantity ?? 0,
            'quantity_after' => $newInventory->quantity ?? 0,
            'serial_number' => $newInventory->serial_number ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log inventory consumption (when pole installed)
     */
    public function logConsumed($dispatch, string $inventoryType, $pole = null): InventoryHistory
    {
        $metadata = [
            'dispatch_id' => $dispatch->id,
            'vendor_id' => $dispatch->vendor_id,
        ];

        if ($pole) {
            $metadata['pole_id'] = $pole->id;
            $metadata['complete_pole_number'] = $pole->complete_pole_number ?? null;
            $metadata['site_id'] = $pole->task->site_id ?? null;
        }

        return $this->log('consumed', [
            'inventory_id' => null, // Dispatch doesn't directly reference inventory_id
            'inventory_type' => $inventoryType,
            'project_id' => $dispatch->project_id,
            'store_id' => $dispatch->store_id,
            'quantity_before' => 1, // Before consumption
            'quantity_after' => 0, // After consumption
            'serial_number' => $dispatch->serial_number,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log inventory lock
     */
    public function logLocked(int $projectId, string $reason = null): InventoryHistory
    {
        return $this->log('locked', [
            'project_id' => $projectId,
            'metadata' => [
                'lock_reason' => $reason,
            ],
        ]);
    }

    /**
     * Log inventory unlock
     */
    public function logUnlocked(int $projectId): InventoryHistory
    {
        return $this->log('unlocked', [
            'project_id' => $projectId,
        ]);
    }
}


<?php

namespace App\Services\Task;

use App\Contracts\TaskRepositoryInterface;
use App\Models\InventoryDispatch;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Task Material Service
 * 
 * Handles material tracking and consumption for tasks
 */
class TaskMaterialService extends BaseService
{
    /**
     * Create new TaskMaterialService instance
     * 
     * @param TaskRepositoryInterface $repository
     */
    public function __construct(
        protected TaskRepositoryInterface $repository
    ) {
    }

    /**
     * Link dispatched materials to task
     * 
     * @param int $taskId
     * @param array $materialIds Array of inventory_dispatch IDs
     * @return array
     */
    public function linkMaterialsToTask(int $taskId, array $materialIds): array
    {
        return $this->executeInTransaction(function () use ($taskId, $materialIds) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new \InvalidArgumentException("Task with ID {$taskId} not found");
            }

            $linkedMaterials = [];

            foreach ($materialIds as $materialId) {
                $dispatch = InventoryDispatch::find($materialId);

                if ($dispatch) {
                    // Link material to task (you might have a pivot table for this)
                    // For now, we'll update the task's materials_consumed field
                    $linkedMaterials[] = [
                        'dispatch_id' => $dispatch->id,
                        'item_code' => $dispatch->item_code ?? $dispatch->product_name,
                        'quantity' => $dispatch->quantity,
                    ];
                }
            }

            // Update task with material information
            if (!empty($linkedMaterials)) {
                $task->materials_consumed = json_encode($linkedMaterials);
                $task->save();
            }

            $this->logInfo('Materials linked to task', [
                'task_id' => $taskId,
                'material_count' => count($linkedMaterials)
            ]);

            return $linkedMaterials;
        });
    }

    /**
     * Record material consumption for task
     * 
     * @param int $taskId
     * @param int $dispatchId
     * @param array $consumptionData
     * @return bool
     */
    public function recordMaterialConsumption(int $taskId, int $dispatchId, array $consumptionData): bool
    {
        return $this->executeInTransaction(function () use ($taskId, $dispatchId, $consumptionData) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new \InvalidArgumentException("Task with ID {$taskId} not found");
            }

            $dispatch = InventoryDispatch::find($dispatchId);

            if (!$dispatch) {
                throw new \InvalidArgumentException("Inventory dispatch with ID {$dispatchId} not found");
            }

            // Mark material as consumed
            $dispatch->is_consumed = true;
            $dispatch->consumed_at = now();
            $dispatch->consumed_by = auth()->id();

            // Add serial numbers if provided
            if (isset($consumptionData['serial_numbers'])) {
                $dispatch->serial_numbers = $consumptionData['serial_numbers'];
            }

            // Link to pole/site if provided
            if (isset($consumptionData['pole_id'])) {
                $dispatch->pole_id = $consumptionData['pole_id'];
            }

            $dispatch->save();

            $this->logInfo('Material consumption recorded', [
                'task_id' => $taskId,
                'dispatch_id' => $dispatchId
            ]);

            return true;
        });
    }

    /**
     * Validate material availability before task assignment
     * 
     * @param int $projectId
     * @param array $requiredMaterials
     * @return array
     */
    public function validateMaterialAvailability(int $projectId, array $requiredMaterials): array
    {
        $availability = [];

        foreach ($requiredMaterials as $material) {
            $itemCode = $material['item_code'] ?? $material['product_name'];
            $requiredQty = $material['quantity'];

            // Check available stock
            $availableQty = InventoryDispatch::where('project_id', $projectId)
                ->where(function ($query) use ($itemCode) {
                    $query->where('item_code', $itemCode)
                        ->orWhere('product_name', $itemCode);
                })
                ->where('is_consumed', false)
                ->sum('quantity');

            $availability[] = [
                'item_code' => $itemCode,
                'required_quantity' => $requiredQty,
                'available_quantity' => $availableQty,
                'is_sufficient' => $availableQty >= $requiredQty,
                'shortage' => max(0, $requiredQty - $availableQty),
            ];
        }

        return $availability;
    }

    /**
     * Track serial numbers of installed components
     * 
     * @param int $taskId
     * @param array $serialNumbers
     * @return bool
     */
    public function trackSerialNumbers(int $taskId, array $serialNumbers): bool
    {
        return $this->executeInTransaction(function () use ($taskId, $serialNumbers) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new \InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Store serial numbers in task description or separate field
            $serialNumberText = "Serial Numbers:\n" . implode("\n", $serialNumbers);
            
            $task->description = $task->description 
                ? $task->description . "\n\n" . $serialNumberText
                : $serialNumberText;
            
            $task->save();

            $this->logInfo('Serial numbers tracked', [
                'task_id' => $taskId,
                'serial_count' => count($serialNumbers)
            ]);

            return true;
        });
    }

    /**
     * Calculate material usage for task
     * 
     * @param int $taskId
     * @return array
     */
    public function calculateMaterialUsage(int $taskId): array
    {
        $task = $this->repository->findWithFullRelations($taskId);

        if (!$task) {
            throw new \InvalidArgumentException("Task with ID {$taskId} not found");
        }

        // Get dispatched materials for this task's project
        $dispatchedMaterials = InventoryDispatch::where('project_id', $task->project_id)
            ->where('vendor_id', $task->vendor_id)
            ->get();

        $totalAllocated = $dispatchedMaterials->sum('quantity');
        $totalConsumed = $dispatchedMaterials->where('is_consumed', true)->sum('quantity');

        return [
            'allocated_quantity' => $totalAllocated,
            'consumed_quantity' => $totalConsumed,
            'remaining_quantity' => $totalAllocated - $totalConsumed,
            'utilization_percentage' => $totalAllocated > 0 
                ? round(($totalConsumed / $totalAllocated) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get materials consumed by task
     * 
     * @param int $taskId
     * @return array
     */
    public function getTaskMaterials(int $taskId): array
    {
        $task = $this->repository->findById($taskId);

        if (!$task) {
            throw new \InvalidArgumentException("Task with ID {$taskId} not found");
        }

        if ($task->materials_consumed) {
            return json_decode($task->materials_consumed, true) ?? [];
        }

        return [];
    }
}

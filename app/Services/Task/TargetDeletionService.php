<?php

namespace App\Services\Task;

use App\Models\Pole;
use App\Models\StreetlightTask;
use App\Models\Streetlight;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Services\BaseService;
use App\Services\Inventory\InventoryHistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TargetDeletionService extends BaseService
{
    protected InventoryHistoryService $historyService;

    public function __construct(InventoryHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Delete targets and handle all related cleanup
     *
     * @param array $taskIds
     * @param string|null $jobId
     * @return array
     */
    public function deleteTargets(array $taskIds, ?string $jobId = null): array
    {
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'TargetDeletionService.php:33','message'=>'deleteTargets called','data'=>['task_ids'=>$taskIds,'job_id'=>$jobId],'timestamp'=>time()*1000])."\n",FILE_APPEND);
        // #endregion
        $result = [
            'poles_deleted' => 0,
            'surveyed_poles_deleted' => 0,
            'installed_poles_deleted' => 0,
            'inventory_items_returned' => 0,
            'dispatches_deleted' => 0,
            'site_updates' => [],
        ];

        foreach ($taskIds as $taskId) {
            // Wrap each task deletion in its own transaction for atomicity
            DB::beginTransaction();
            
            try {
                $task = StreetlightTask::with('poles')->find($taskId);
                // #region agent log
                file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'TargetDeletionService.php:52','message'=>'Task found, processing deletion','data'=>['task_id'=>$taskId,'poles_count'=>$task?->poles->count()??0],'timestamp'=>time()*1000])."\n",FILE_APPEND);
                // #endregion
                
                if (!$task) {
                    Log::warning('Task not found for deletion', ['task_id' => $taskId]);
                    DB::rollBack();
                    continue;
                }

                // Get poles before deletion to count surveyed/installed
                $poles = $task->poles;
                $surveyedCount = $poles->where('isSurveyDone', 1)->count();
                $installedCount = $poles->where('isInstallationDone', 1)->count();

                // Process each pole: return inventory and delete dispatches
                foreach ($poles as $pole) {
                    $poleResult = $this->processPoleDeletion($pole);
                    $result['poles_deleted']++;
                    $result['inventory_items_returned'] += $poleResult['inventory_returned'];
                    $result['dispatches_deleted'] += $poleResult['dispatches_deleted'];
                }

                $result['surveyed_poles_deleted'] += $surveyedCount;
                $result['installed_poles_deleted'] += $installedCount;

                // Update streetlight counts
                if ($task->site_id) {
                    // #region agent log
                    file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'TargetDeletionService.php:75','message'=>'Updating streetlight counts','data'=>['site_id'=>$task->site_id,'surveyed_count'=>$surveyedCount,'installed_count'=>$installedCount],'timestamp'=>time()*1000])."\n",FILE_APPEND);
                    // #endregion
                    $this->updateStreetlightCounts(
                        $task->site_id,
                        $surveyedCount,
                        $installedCount
                    );
                    $result['site_updates'][] = $task->site_id;
                }

                // Delete poles
                Pole::where('task_id', $taskId)->delete();

                // Delete the task
                $task->delete();
                // #region agent log
                file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'TargetDeletionService.php:89','message'=>'Task and poles deleted, committing transaction','data'=>['task_id'=>$taskId],'timestamp'=>time()*1000])."\n",FILE_APPEND);
                // #endregion

                // Commit transaction for this task
                DB::commit();
                
                Log::info('Successfully deleted task and related data', [
                    'task_id' => $taskId,
                    'poles_deleted' => $poles->count(),
                    'surveyed_poles' => $surveyedCount,
                    'installed_poles' => $installedCount
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error deleting target', [
                    'task_id' => $taskId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'TargetDeletionService.php:110','message'=>'deleteTargets completed','data'=>['total_poles_deleted'=>$result['poles_deleted'],'inventory_returned'=>$result['inventory_items_returned']],'timestamp'=>time()*1000])."\n",FILE_APPEND);
        // #endregion

        return $result;
    }

    /**
     * Process deletion for a single pole
     *
     * @param Pole $pole
     * @return array
     */
    protected function processPoleDeletion(Pole $pole): array
    {
        $result = [
            'inventory_returned' => 0,
            'dispatches_deleted' => 0,
        ];

        // Track processed dispatch IDs to avoid duplicates
        $processedDispatchIds = [];

        // Get inventory dispatches for this pole (by streetlight_pole_id)
        $dispatchesByPole = InventoryDispatch::where('streetlight_pole_id', $pole->id)->get();

        foreach ($dispatchesByPole as $dispatch) {
            try {
                $this->returnInventoryFromDispatch($dispatch);
                $processedDispatchIds[] = $dispatch->id;
                $result['inventory_returned']++;
                $result['dispatches_deleted']++;
            } catch (\Exception $e) {
                Log::error('Error processing dispatch for pole', [
                    'pole_id' => $pole->id,
                    'dispatch_id' => $dispatch->id,
                    'error' => $e->getMessage()
                ]);
                // Continue with next dispatch
                continue;
            }
        }

        // Also check for dispatches by serial numbers from pole
        // Handle panel_qr, battery_qr, luminary_qr
        $serialNumbers = array_filter([
            $pole->panel_qr,
            $pole->battery_qr,
            $pole->luminary_qr,
        ]);

        foreach ($serialNumbers as $serialNumber) {
            if (empty($serialNumber)) {
                continue;
            }

            // Find dispatches by serial number that aren't already processed
            $dispatchesBySerial = InventoryDispatch::where('serial_number', $serialNumber)
                ->whereNotIn('id', $processedDispatchIds)
                ->get();

            foreach ($dispatchesBySerial as $dispatch) {
                $this->returnInventoryFromDispatch($dispatch);
                $processedDispatchIds[] = $dispatch->id;
                $result['inventory_returned']++;
                $result['dispatches_deleted']++;
            }
        }

        // Handle SL02 (luminary) with sim_number
        if ($pole->luminary_qr && $pole->sim_number) {
            // Find by serial number first
            $luminaryBySerial = InventoryDispatch::where('serial_number', $pole->luminary_qr)
                ->whereNotIn('id', $processedDispatchIds)
                ->get();

            foreach ($luminaryBySerial as $dispatch) {
                $this->returnInventoryFromDispatch($dispatch);
                $processedDispatchIds[] = $dispatch->id;
                $result['inventory_returned']++;
                $result['dispatches_deleted']++;
            }

            // Also find by sim_number in inventory_streetlight
            $luminaryBySim = InventoryDispatch::whereHas('inventoryStreetLight', function ($q) use ($pole) {
                    $q->where('item_code', 'SL02')
                      ->where('sim_number', $pole->sim_number);
                })
                ->whereNotIn('id', $processedDispatchIds)
                ->get();

            foreach ($luminaryBySim as $dispatch) {
                $this->returnInventoryFromDispatch($dispatch);
                $processedDispatchIds[] = $dispatch->id;
                $result['inventory_returned']++;
                $result['dispatches_deleted']++;
            }
        }

        return $result;
    }

    /**
     * Return inventory from dispatch to stock
     *
     * @param InventoryDispatch $dispatch
     * @return void
     */
    protected function returnInventoryFromDispatch(InventoryDispatch $dispatch): void
    {
        // Note: This method is called within a transaction from processPoleDeletion
        // So we don't need another transaction wrapper here
        
        try {
            // Find the inventory item by serial number
            $inventory = InventroyStreetLightModel::where('serial_number', $dispatch->serial_number)
                ->first();

            if ($inventory) {
                // Set quantity to 1 (restore to stock)
                $inventory->quantity = 1;
                $inventory->save();

                // Log history
                $project = \App\Models\Project::find($dispatch->project_id);
                $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                
                $this->historyService->logReturned(
                    $inventory,
                    $inventoryType,
                    $dispatch->project_id,
                    $dispatch->store_id,
                    1
                );
            } else {
                // Log warning if inventory not found, but continue with dispatch deletion
                Log::warning('Inventory item not found for dispatch return', [
                    'dispatch_id' => $dispatch->id,
                    'serial_number' => $dispatch->serial_number,
                ]);
            }

            // Delete the dispatch record
            $dispatch->delete();
        } catch (\Exception $e) {
            Log::error('Error returning inventory from dispatch', [
                'dispatch_id' => $dispatch->id,
                'serial_number' => $dispatch->serial_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update streetlight counts after pole deletion
     *
     * @param int $siteId
     * @param int $surveyedCount
     * @param int $installedCount
     * @return void
     */
    protected function updateStreetlightCounts(int $siteId, int $surveyedCount, int $installedCount): void
    {
        try {
            $streetlight = Streetlight::find($siteId);
            
            if (!$streetlight) {
                Log::warning('Streetlight not found for count update', ['site_id' => $siteId]);
                return;
            }

            // Decrement counts, ensuring they don't go below 0
            $newSurveyed = max(0, ($streetlight->number_of_surveyed_poles ?? 0) - $surveyedCount);
            $newInstalled = max(0, ($streetlight->number_of_installed_poles ?? 0) - $installedCount);

            $streetlight->update([
                'number_of_surveyed_poles' => $newSurveyed,
                'number_of_installed_poles' => $newInstalled,
            ]);

            Log::info('Streetlight counts updated', [
                'site_id' => $siteId,
                'surveyed_deleted' => $surveyedCount,
                'installed_deleted' => $installedCount,
                'new_surveyed' => $newSurveyed,
                'new_installed' => $newInstalled,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating streetlight counts', [
                'site_id' => $siteId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}


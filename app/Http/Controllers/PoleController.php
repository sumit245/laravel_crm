<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryHistoryService;
//TODO: Add StreetlightTask and User model also to modify vendor(installer name, billing status etc)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PoleController extends Controller
{
    protected InventoryService $inventoryService;
    protected InventoryHistoryService $historyService;

    public function __construct(InventoryService $inventoryService, InventoryHistoryService $historyService)
    {
        $this->inventoryService = $inventoryService;
        $this->historyService = $historyService;
    }
    public function edit($id)
    {
        $pole = Pole::findOrFail($id);

        // Get related data (same as show method)
        $installer = $pole->task->installer ?? null;
        $siteEngineer = $pole->task->engineer ?? null;
        $projectManager = $pole->task->streetlight->projectManager ?? null;

        return view('poles.edit', compact('pole', 'installer', 'siteEngineer', 'projectManager'));
    }

    public function update(Request $request, $id)
    {
        $pole = Pole::findOrFail($id);

        $rules = [
            'ward_name' => 'nullable|string|max:255',
            'beneficiary' => 'nullable|string|max:255',
            'beneficiary_contact' => 'nullable|string|max:20',
            'isSurveyDone' => 'nullable|boolean',
            'isInstallationDone' => 'nullable|boolean',
            'isNetworkAvailable' => 'nullable|boolean',
            'vendor_id' => 'nullable|string|max:255',
            'luminary_qr' => 'nullable|string|max:255',
            'sim_number' => 'nullable|string|max:200',
            'panel_qr' => 'nullable|string|max:255',
            'battery_qr' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'remarks' => 'nullable|string',
        ];

        if (
            $request->filled('luminary_qr')
            && $request->luminary_qr !== $pole->luminary_qr
            && $request->sim_number === $pole->sim_number
        ) {
            $rules['sim_number'] = 'required|string|max:200|different:sim_number';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Get pole's district for validation
            $poleDistrict = null;
            if ($pole->task && $pole->task->streetlight) {
                $poleDistrict = $pole->task->streetlight->district;
            }

            // Return inventory if any QR changed
            foreach (['luminary_qr', 'panel_qr', 'battery_qr'] as $field) {
                if (!empty($validated[$field]) && $validated[$field] !== $pole->$field) {
                    // Validate new serial exists and district matches
                    $newDispatch = InventoryDispatch::where('serial_number', $validated[$field])
                        ->where('isDispatched', true)
                        ->where('is_consumed', false)
                        ->first();

                    if ($newDispatch && $poleDistrict) {
                        // Check district match
                        $projectDistricts = $this->inventoryService->getProjectDistricts($newDispatch->project_id);
                        if (!in_array($poleDistrict, $projectDistricts)) {
                            DB::rollBack();
                            $project = \App\Models\Project::find($newDispatch->project_id);
                            return redirect()->back()
                                ->withErrors([$field => "Inventory from {$project->project_name} cannot be used in district {$poleDistrict}"])
                                ->withInput();
                        }
                    }

                    $this->replaceSerialManually($pole, $field, $validated[$field], $poleDistrict);
                }
            }



            // Update pole
            $pole->update([
                'ward_name' => $validated['ward_name'] ?? $pole->ward_name,
                'beneficiary' => $validated['beneficiary'] ?? $pole->beneficiary,
                'beneficiary_contact' => $validated['beneficiary_contact'] ?? $pole->beneficiary_contact,
                'remarks' => $validated['remarks'] ?? $pole->remarks,
                'luminary_qr' => $validated['luminary_qr'] ?? $pole->luminary_qr,
                'sim_number' => $validated['sim_number'] ?? $pole->sim_number,
                'panel_qr' => $validated['panel_qr'] ?? $pole->panel_qr,
                'battery_qr' => $validated['battery_qr'] ?? $pole->battery_qr,
                'lat' => $validated['lat'] ?? $pole->lat,
                'lng' => $validated['lng'] ?? $pole->lng,
                'isSurveyDone' => $validated['isSurveyDone'] ?? $pole->isSurveyDone,
                'isInstallationDone' => $validated['isInstallationDone'] ?? $pole->isInstallationDone,
                'isNetworkAvailable' => $validated['isNetworkAvailable'] ?? $pole->isNetworkAvailable,
            ]);

            // Mark new inventory as consumed if QR codes are provided
            $newSerials = array_filter([
                $validated['luminary_qr'] ?? null,
                $validated['panel_qr'] ?? null,
                $validated['battery_qr'] ?? null,
            ]);

            if (!empty($newSerials)) {
                // Validate district for new serials before consuming
                $poleDistrict = $pole->task && $pole->task->streetlight ? $pole->task->streetlight->district : null;
                
                if ($poleDistrict) {
                    $dispatches = InventoryDispatch::whereIn('serial_number', $newSerials)
                        ->whereNull('streetlight_pole_id')
                        ->get();

                    foreach ($dispatches as $dispatch) {
                        $projectDistricts = $this->inventoryService->getProjectDistricts($dispatch->project_id);
                        if (!in_array($poleDistrict, $projectDistricts)) {
                            DB::rollBack();
                            $project = \App\Models\Project::find($dispatch->project_id);
                            return redirect()->back()
                                ->withErrors(['error' => "Inventory from {$project->project_name} cannot be used in district {$poleDistrict}"])
                                ->withInput();
                        }
                    }
                }

                $dispatches = InventoryDispatch::whereIn('serial_number', $newSerials)
                    ->whereNull('streetlight_pole_id')
                    ->get();

                foreach ($dispatches as $dispatch) {
                    $dispatch->update([
                        'is_consumed' => true,
                        'streetlight_pole_id' => $pole->id,
                    ]);

                    // Log history
                    $project = \App\Models\Project::find($dispatch->project_id);
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logConsumed($dispatch, $inventoryType, $pole);
                }
            }

            DB::commit();
            Log::info('Pole Updated:', $pole->toArray());

            return redirect()->route('poles.show', $pole->id)
                ->with('success', 'Pole details updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update pole', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to update pole details: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
{
    try {
        $pole = Pole::findOrFail($id);

        // Count poles with same pole number
        $samePolesCount = Pole::where('complete_pole_number', $pole->pole_number)->count();

        // Get associated streetlight
        $streetlight = $pole->task->streetlight ?? null;

        // Check inventory usage
        $inventoryFields = ['luminary_qr', 'panel_qr', 'battery_qr'];
        $isInventoryUsed = false;

        foreach ($inventoryFields as $field) {
            if (!empty($pole->$field)) {
                $dispatch = InventoryDispatch::where('serial_number', $pole->$field)
                    ->where('streetlight_pole_id', '!=', $pole->id)
                    ->first();

                if ($dispatch) {
                    $isInventoryUsed = true;
                    break;
                }
            }
        }

        // If multiple poles exist with same pole number
        if ($samePolesCount > 1) {
            if ($isInventoryUsed) {
                // Inventory used in other pole → directly delete
                $pole->delete();
            } else {
                // Inventory not used elsewhere → return inventory + delete
                foreach ($inventoryFields as $field) {
                    if (!empty($pole->$field)) {
                        $this->returnInventoryItem($pole->$field);
                    }
                }
                $pole->delete();

                if ($streetlight) {
                    $streetlight->decrement('polecount', 1);
                }
            }
        } else {
            // Only one pole with that number → always return inventory + delete
            foreach ($inventoryFields as $field) {
                if (!empty($pole->$field)) {
                    $this->returnInventoryItem($pole->$field);
                }
            }
            $pole->delete();

            if ($streetlight) {
                $streetlight->decrement('polecount', 1);
            }
        }

        return redirect()->route('poles.index')
            ->with('success', 'Pole deleted successfully.');
    } catch (\Exception $e) {
        Log::error('Failed to delete pole', ['error' => $e->getMessage()]);
        return redirect()->back()
            ->with('error', 'Failed to delete pole.');
    }
}

    private function returnInventoryItem($serialNumber)
    {
        try {
            Log::info('Returning inventory item', ['serial_number' => $serialNumber]);

            // Find inventory item
            $inventory = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();

            // Find dispatch record
            $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                ->whereNotNull('streetlight_pole_id')
                ->first();

            if ($inventory) {
                $inventory->quantity = 1;
                $inventory->save();
                Log::info('Inventory quantity updated', ['serial_number' => $serialNumber]);
            }

            if ($dispatch) {
                $dispatch->delete();
                Log::info('Dispatch record updated', ['serial_number' => $serialNumber]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to return inventory item', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function replaceSerialManually($pole, $field, $newSerial, $poleDistrict = null)
    {
        $oldSerial = $pole->$field;

        // Step 1: Get old dispatch record
        $oldDispatch = InventoryDispatch::where('serial_number', $oldSerial)->first();
        if (!$oldDispatch) return;

        // Step 2: Validate new serial exists in inventory
        $newInventoryItem = InventroyStreetLightModel::where('serial_number', $newSerial)->first();
        if (!$newInventoryItem) {
            throw new \Exception("Serial number {$newSerial} not found in inventory");
        }

        // Step 3: Check if new serial is already consumed
        $existingConsumed = InventoryDispatch::where('serial_number', $newSerial)
            ->where('is_consumed', true)
            ->exists();
        if ($existingConsumed) {
            throw new \Exception("Serial number {$newSerial} is already consumed");
        }

        // Step 4: Check or clone new dispatch
        $newDispatch = InventoryDispatch::where('serial_number', $newSerial)
            ->where('isDispatched', true)
            ->where('is_consumed', false)
            ->first();

        if (!$newDispatch) {
            $newDispatch = $oldDispatch->replicate();
            $newDispatch->serial_number = $newSerial;
            $newDispatch->is_consumed = false;
            $newDispatch->streetlight_pole_id = null;
            $newDispatch->save();
        } else {
            // Validate district if pole district is available
            if ($poleDistrict) {
                $projectDistricts = $this->inventoryService->getProjectDistricts($newDispatch->project_id);
                if (!in_array($poleDistrict, $projectDistricts)) {
                    $project = \App\Models\Project::find($newDispatch->project_id);
                    throw new \Exception("Inventory from {$project->project_name} cannot be used in district {$poleDistrict}");
                }
            }

            $newDispatch->fill($oldDispatch->only([
                'vendor_id',
                'total_quantity',
                'total_value',
                'rate',
                'store_id',
                'store_incharge_id',
                'project_id',
                'isDispatched',
                'is_consumed',
                'streetlight_pole_id'
            ]));
            $newDispatch->save();
        }

        // Step 5: Update inventory streetlight model - consume new, return old
        $newStreet = InventroyStreetLightModel::where('serial_number', $newSerial)->first();
        if ($newStreet) {
            $newStreet->quantity = 0; // Consume new item
            $newStreet->save();
        } else {
            $oldStreet = InventroyStreetLightModel::where('serial_number', $oldSerial)->first();
            if ($oldStreet) {
                $newStreet = $oldStreet->replicate();
                $newStreet->serial_number = $newSerial;
                $newStreet->quantity = 0; // Consume new item
                $newStreet->save();
            }
        }

        // Step 6: Return old item to stock
        $oldStreet = InventroyStreetLightModel::where('serial_number', $oldSerial)->first();
        if ($oldStreet) {
            $oldStreet->quantity = 1; // Return old item to stock
            $oldStreet->save();
        }

        // Step 7: Log history for replacement
        $project = \App\Models\Project::find($oldDispatch->project_id);
        $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
        
        if (isset($oldStreet) && isset($newStreet)) {
            $this->historyService->logReplaced(
                $oldStreet,
                $newStreet,
                $inventoryType,
                $oldDispatch->project_id,
                $oldDispatch->store_id,
                $pole
            );
        }

        // Step 8: Delete old dispatch
        $oldDispatch->delete();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
//TODO: Add StreetlightTask and User model also to modify vendor(installer name, billing status etc)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PoleController extends Controller
{
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


            // Return inventory if any QR changed
            foreach (['luminary_qr', 'panel_qr', 'battery_qr'] as $field) {
                if (!empty($validated[$field]) && $validated[$field] !== $pole->$field) {
                    $this->replaceSerialManually($pole, $field, $validated[$field]);
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
                InventoryDispatch::whereIn('serial_number', $newSerials)
                    ->whereNull('streetlight_pole_id')
                    ->update([
                        'is_consumed' => true,
                        'streetlight_pole_id' => $pole->id,
                    ]);
            }

            Log::info('Pole Updated:', $pole->toArray());

            return redirect()->route('poles.show', $pole->id)
                ->with('success', 'Pole details updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update pole', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to update pole details')
                ->withInput();
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

    protected function replaceSerialManually($pole, $field, $newSerial)
    {
        $oldSerial = $pole->$field;

        // Step 1: Get old dispatch record
        $oldDispatch = InventoryDispatch::where('serial_number', $oldSerial)->first();
        if (!$oldDispatch) return;

        // Step 2: Check or clone new dispatch
        $newDispatch = InventoryDispatch::where('serial_number', $newSerial)->first();

        if (!$newDispatch) {
            $newDispatch = $oldDispatch->replicate();
            $newDispatch->serial_number = $newSerial;
            $newDispatch->is_consumed = false;
            $newDispatch->streetlight_pole_id = null;
            $newDispatch->save();
        } else {
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

        // Step 3: Update inventory streetlight model
        $newStreet = InventroyStreetLightModel::where('serial_number', $newSerial)->first();
        if ($newStreet) {
            $newStreet->quantity = 0;
            $newStreet->save();
        } else {
            $oldStreet = InventroyStreetLightModel::where('serial_number', $oldSerial)->first();
            if ($oldStreet) {
                $newStreet = $oldStreet->replicate();
                $newStreet->serial_number = $newSerial;
                $newStreet->quantity = 0;
                $newStreet->save();
            }
        }

        // Step 4: Delete old dispatch
        $oldDispatch->delete();

        // Step 5: Restore quantity of old item in inventory streetlight
        if (isset($oldStreet)) {
            $oldStreet->quantity = 1;
            $oldStreet->save();
        }

        // Step 6: You don't need to update the pole here — your `update()` method will handle it.
    }
}

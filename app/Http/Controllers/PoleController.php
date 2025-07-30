<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
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
        // Validation
        $validated = $request->validate([
            'ward_name' => 'nullable|string|max:255',
            'beneficiary' => 'nullable|string|max:255',
            'beneficiary_contact' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
            'luminary_qr' => 'nullable|string|max:255',
            'sim_number' => 'nullable|string|max:200',
            'panel_qr' => 'nullable|string|max:255',
            'battery_qr' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'isSurveyDone' => 'nullable|boolean',
            'isInstallationDone' => 'nullable|boolean',
            'isNetworkAvailable' => 'nullable|boolean',
        ]);

        try {
            $pole = Pole::findOrFail($id);
            
            // Store old QR codes for inventory return
            $oldQRCodes = [
                'luminary_qr' => $pole->luminary_qr,
                'panel_qr' => $pole->panel_qr,
                'battery_qr' => $pole->battery_qr,
            ];

            // Check if QR codes have changed and return old inventory
            foreach (['luminary_qr', 'panel_qr', 'battery_qr'] as $qrField) {
                $oldQR = $oldQRCodes[$qrField];
                $newQR = $validated[$qrField] ?? null;
                
                // If QR code changed and old one exists, return it to inventory
                if ($oldQR && $oldQR !== $newQR) {
                    $this->returnInventoryItem($oldQR);
                }
            }

            // Update pole with new data
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
                $dispatch->update([
                    'is_consumed' => false,
                    'streetlight_pole_id' => null,
                ]);
                Log::info('Dispatch record updated', ['serial_number' => $serialNumber]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to return inventory item', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage()
            ]);
        }
    }
}

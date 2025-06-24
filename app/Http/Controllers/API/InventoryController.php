<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\InventoryImport;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inventory::with(['project', 'site'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'productName'     => 'required|string',
                'brand'           => 'required|string',
                'description'     => 'nullable|string',
                'unit'            => 'required|string',
                'initialQuantity' => 'string',
                'quantityStock'   => 'string',
            ]);
            $inventory = Inventory::create($validated);
            return response()->json([
                'message' => 'Inventory created successfully',
                'data'    => $inventory,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Inventory::with(['project', 'site', 'task'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());
        return $inventory;
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new InventoryImport($request->project_id, $request->storeId), $request->file('file'));
            return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'Inventory deleted']);
    }
    // Dispatch Inventory to a vendor
    public function dispatchInventory(Request $request)
    {
        try {
            $request->validate([
                'vendor_id' => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
                'store_id' => 'required|exists:stores,id',
                'store_incharge_id' => 'required|exists:users,id',
                'items' => 'required|array',
                'items.*.inventory_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $project = Project::findOrFail($request->project_id);
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;
            $dispatchedItems = [];

            foreach ($request->items as $item) {
                $inventory = $inventoryModel::where('id', $item['inventory_id'])
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id) // Ensure it belongs to correct store
                    ->first();

                if (!$inventory) {
                    return response()->json([
                        'message' => "Inventory ID {$item['inventory_id']} not found for this project"
                    ], 404);
                }
                if ($inventory->quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Not enough stock for item {$inventory->item}",
                    ], 400);
                }
                // Store dispatch details
                $dispatch = InventoryDispatch::create([
                    'vendor_id' => $request->vendor_id,
                    'project_id' => $request->project_id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'dispatch_date' => Carbon::now(),
                    'store_id' => $request->store_id,
                    'store_incharge_id' => $request->store_incharge_id,
                ]);
                // Reduce stock from correct table
                $inventory->decrement('quantity', $item['quantity']);
                $dispatchedItems[] = $dispatch;
            }


            return response()->json([
                'success' => true,
                'message' => 'Inventory dispatched successfully',
                'data' => $dispatchedItems
            ], 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function replaceItem(Request $request)
    {
        Log::info($request->all());
        $request->validate([
            'old_serial_number' => 'required|string',
            'new_serial_number' => 'required|string|different:old_serial_number',
            'auth_code' => 'required|string',

        ]);

        // Step 0: Validate auth code
        $expectedCode = env('REPLACEMENT_AUTH_CODE'); // Define this in .env
        if ($request->auth_code !== $expectedCode) {
            return back()->with('error', 'Invalid authentication code.');
        }

        try {
            DB::beginTransaction();

            // Step 1: Get old item
            $oldItem = InventroyStreetLightModel::where('serial_number', $request->old_serial_number)->firstOrFail();
            $storeId = $oldItem->store_id;

            // Step 2: Check if new item exists in same store
            $newItem = InventroyStreetLightModel::where('serial_number', $request->new_serial_number)
                ->where('store_id', $storeId)
                ->first();

            if (!$newItem) {
                // Insert new item with same details as old one, except serial number
                $newItem = $oldItem->replicate();
                $newItem->serial_number = $request->new_serial_number;
                $newItem->quantity = 0; // Set default
                $newItem->save();
            }

            // Step 3: Check if new item is already dispatched
            $alreadyDispatched = InventoryDispatch::where('serial_number', $request->new_serial_number)->exists();

            if (!$alreadyDispatched) {
                $oldDispatch = InventoryDispatch::where('serial_number', $request->old_serial_number)->first();
                if ($oldDispatch) {
                    $newDispatch = $oldDispatch->replicate();
                    $newDispatch->serial_number = $request->new_serial_number;
                    $newDispatch->save();
                }
            }

            // Step 4: Delete old dispatch
            InventoryDispatch::where('serial_number', $request->old_serial_number)->delete();

            // Step 5: Update quantity fields
            $oldItem->update(['quantity' => 1]);
            $newItem->update(['quantity' => 0]);

            DB::commit();

            return back()->with('success', 'Serial number replaced successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Replacement failed: ' . $e->getMessage());
        }
    }
}

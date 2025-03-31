<?php

namespace App\Http\Controllers;

use Exception;
use App\Imports\InventoryImport;
use App\Imports\InventroyStreetLight;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use Carbon\Carbon;
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
        //
        $inventory = Inventory::all();
        return view('inventory.index', compact('inventory'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        $projectId = $request->projectId;
        $storeId   = $request->storeId;
        Log::info($storeId);
        try {
            Excel::import(new InventoryImport($projectId, $storeId), $request->file('file'));
            return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
        } catch (\Exception $e) {
            //    return alert('Error importing inventory: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function importStreetlight(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        $projectId = $request->projectId;
        $storeId   = $request->storeId;
        Log::info($storeId);
        try {
            Excel::import(new InventroyStreetLight($projectId, $storeId), $request->file('file'));
            return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
        } catch (\Exception $e) {
            //    return alert('Error importing inventory: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'productName'     => 'required|string|max:255',
            'brand'           => 'nullable|string',
            'description'     => 'nullable|string',
            'initialQuantity' => 'required|string',
            'quantityStock'   => 'nullable|string',
            'unit'            => 'required|string|max:25',
            'receivedDate'    => 'nullable|date',
        ]);

        try {

            $inventory = Inventory::create($validated);

            return redirect()->route('inventory.show', $inventory->id)
                ->with('success', 'Inventory created successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $item = Inventory::findOrFail($id);
        return view('inventory.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $item = Inventory::findOrFail($id);
        return view('inventory.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $item)
    {
        //
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'productName'     => 'required|string|max:255',
            'brand'           => 'nullable|string',
            'description'     => 'nullable|string',
            'initialQuantity' => 'required|string',
            'quantityStock'   => 'nullable|string',
            'unit'            => 'required|string|max:25',
            'receivedDate'    => 'nullable|date',
        ]);

        try {

            $item->update($validated);
            return redirect()->route('inventory.show', compact('item'))
                ->with('success', 'Inventory updated successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            Inventory::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete Item'], 500);
        }
    }

    // View Inventory from store
    public function viewInventory(Request $request)
    {

        try {
            $projectId = $request->project_id;
            $storeId = $request->store_id;
            $storeName = "";

            // Fetch the project to determine the type
            $project = Project::findOrFail($projectId);
            $projectType = $project->project_type;

            // Determine the model to query based on project type
            // Determine the model to query based on project type
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;


            // Query inventory based on store_name and store_id
            $inventory = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId) // Filter by store_id directly
                ->get();
            return view('inventory.view', compact('inventory', 'projectId', 'storeName', 'projectType'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
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
                'message' => 'Items dispatched successfully',
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

    // Get dispatched inventory for a vendor
    public function viewVendorInventory($vendorId)
    {
        Log::info("Fetching inventory for vendor_id: {$vendorId}");
        try {
            Log::info("Fetching inventory for vendor_id: {$vendorId}");
            $inventory = InventoryDispatch::where('vendor_id', $vendorId)
                ->with(['inventory', 'inventoryStreetLight', 'project', 'store', 'storeIncharge'])
                ->get();

            if ($inventory->isEmpty()) {
                Log::warning("No inventory found for vendor_id: {$vendorId}");
                return response()->json([
                    'message' => 'No inventory found for this vendor.',
                    'vendor_id' => $vendorId
                ], 404);
            }
            // Calculate total inventory value
            $totalInventoryValue = $inventory->sum(function ($item) {
                $unitRate = (float) optional($item->inventoryStreetLight)->rate ?? 0;
                $quantity = (int) $item->quantity ?? 0;
                return (float) $unitRate * $quantity;
            });
            // Restructure response
            $response = [
                'vendor_id' => $vendorId,
                'project_id' => optional($inventory->first()->project)->id,
                'total_inventory_value' => number_format($totalInventoryValue, 2, '.', ''),
                'inventory' => $inventory->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'inventory_id' => $item->inventory_id,
                        'dispatch_date' => $item->dispatch_date,
                        'manufacturer' => optional($item->inventoryStreetLight)->manufacturer,
                        'item_code' => optional($item->inventoryStreetLight)->item_code,
                        'item' => optional($item->inventoryStreetLight)->item,
                        'make' => optional($item->inventoryStreetLight)->make,
                        'model' => optional($item->inventoryStreetLight)->model,
                        'serial_number' => optional($item->inventoryStreetLight)->serial_number,
                        'hsn' => optional($item->inventoryStreetLight)->hsn,
                        'unit' => optional($item->inventoryStreetLight)->unit,
                        'rate' => (float)$item->inventoryStreetLight->rate,
                        'quantity' => (int)$item->quantity ?? 0,  // Ensures it’s never null
                        'total_value' => (float) $item->inventoryStreetLight->rate * (int) $item->quantity, // Proper multiplication
                    ];
                }),
            ];

            return response()->json($response);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

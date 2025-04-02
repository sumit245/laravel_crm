<?php

namespace App\Http\Controllers;

use Exception;
use App\Imports\InventoryImport;
use App\Imports\InventroyStreetLight;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    // Edit Inventory method

    /**
     * Show the form for editing the specified resource.
     */
    public function editInventory($id)
    {
        // Fetch the inventory item by ID
        $inventoryItem = Inventory::findOrFail($id);

        // Return the editInventory view and pass the item data
        return view('inventory.editInventory', compact('inventoryItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateInventory(Request $request, $id)
    {
        // Validate the incoming data
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
            // Find the inventory item
            $inventoryItem = Inventory::findOrFail($id);

            // Update the inventory item
            $inventoryItem->update($validated);

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory updated successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
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
            $store = Stores::findOrFail($storeId);
            $storeName = $store->store_name;
            $storeIncharge = User::findOrFail($store->store_incharge_id);
            $inchargeName = $storeIncharge->firstName . ' ' . $storeIncharge->lastName;

            // Fetch the project to determine the type
            $project = Project::findOrFail($projectId);
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
            return view('inventory.view', compact('inventory', 'projectId', 'storeName', 'inchargeName', 'projectType'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    // Dispatch Inventory to a vendor
    public function dispatchInventory(Request $request)
    {
        Log::info('Dispatch Inventory Request Data:', $request->all());
        try {
            $request->validate([
                'vendor_id' => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
                'store_id' => 'required|exists:stores,id',
                'store_incharge_id' => 'required|exists:users,id',
                'item_code' => 'required|string',
                'item' => 'required|string',
                'rate' => 'required|numeric',
                'make' => 'nullable|string',
                'model' => 'nullable|string',
                'total_quantity' => 'required|integer|min:1',
                'total_value' => 'required|numeric',
                'serial_numbers' => 'required|array',
                'serial_numbers.*' => 'required|string'
            ]);

            $project = Project::findOrFail($request->project_id);
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;
            $dispatchedItems = [];

            // Find inventory items matching the item code
            $inventoryItems = $inventoryModel::where('item_code', $request->item_code)
                ->where('project_id', $request->project_id)
                ->where('store_id', $request->store_id)
                ->get();

            // Check if we have enough inventory
            $availableQuantity = $inventoryItems->sum('quantity');
            if ($availableQuantity < $request->total_quantity) {
                return redirect()->back()->with('error', "Not enough stock for {$request->item}. Available: {$availableQuantity}");
            }
            // For each serial number, find the specific inventory item and dispatch it
            $dispatchedItems = [];
            foreach ($request->serial_numbers as $serialNumber) {
                // Find the specific inventory item with this serial number
                $inventoryItem = $inventoryModel::where('serial_number', $serialNumber)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->first();

                if (!$inventoryItem) {
                    return redirect()->back()->with('error', "Item with serial number {$serialNumber} not found");
                }

                // Create dispatch record
                $dispatch = InventoryDispatch::create([
                    'vendor_id' => $request->vendor_id,
                    'project_id' => $request->project_id,
                    'store_id' => $request->store_id,
                    'store_incharge_id' => $request->store_incharge_id,
                    'item_code' => $request->item_code,
                    'item' => $request->item,
                    'rate' => $request->rate,
                    'make' => $request->make,
                    'model' => $request->model,
                    'total_quantity' => $request->total_quantity,
                    'total_value' => $request->total_value,
                    'serial_numbers' => $serialNumber,
                    'dispatch_date' => Carbon::now(),

                ]);

                // Reduce stock from inventory
                $inventoryItem->decrement('quantity', 1);
                $dispatchedItems[] = $dispatch;
            }

            // Log dispatched items
            Log::info('Dispatched Items:', $dispatchedItems);
            return redirect()->back()->with('success', 'Inventory dispatched successfully');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Inventory dispatched Failed');
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
                        'rate' => (float)$item->inventoryStreetLight->rate??0,
                        'quantity' => (int)$item->quantity ?? 0,  // Ensures it’s never null
                        'total_value' => (float) $item->inventoryStreetLight->rate ??0* (int) $item->quantity, // Proper multiplication
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

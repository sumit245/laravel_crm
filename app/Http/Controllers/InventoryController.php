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
    public function index(Request $request)
    {
        //
        // $inventory = Inventory::all();
        $projectId = $request->query('project_id');
        $storeId = $request->query('store_id');

        // If filtering values are provided, filter the data
        if ($projectId && $storeId) {
            $inventory = InventroyStreetLightModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->get();
        } else {
            // Otherwise return all entries
            $inventory = InventroyStreetLightModel::all();
        }


        return view('inventory.index', compact('inventory'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        $projectId = $request->projectId;
        $storeId   = $request->storeId;
        try {
            Excel::import(new InventoryImport($projectId, $storeId), $request->file('file'));
            return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
        } catch (\Exception $e) {
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
        $projectType = $request->project_type;


        if ($projectType == 1) {
            // Validation for Street Light inventory (project_type == 1)
            $validated = $request->validate([
                'dropdown'      => 'required|string|max:255', // item name
                'code'          => 'required|string|max:255', // item code
                'manufacturer'  => 'required|string|max:255',
                'model'         => 'required|string|max:255',
                'serialnumber'  => 'required|string|max:255',
                'make'          => 'required|string|max:255',
                'rate'          => 'required|numeric',
                'number'        => 'required|numeric', // quantity
                'totalvalue'    => 'required|numeric',
                'hsncode'       => 'required|string|max:255',
                'description'   => 'nullable|string',
                'unit'          => 'required|string|max:255',
                'receiveddate'  => 'required|date',
            ]);

            try {
                InventroyStreetLightModel::create([
                    'project_id'    => $request->input('project_id'),
                    'store_id'       => $request->input('store_id'),
                    'item'      => $validated['dropdown'],
                    'item_code'      => $validated['code'],
                    'manufacturer'   => $validated['manufacturer'],
                    'model'          => $validated['model'],
                    'serial_number'  => $validated['serialnumber'],
                    'make'           => $validated['make'],
                    'rate'           => $validated['rate'],
                    'quantity'       => $validated['number'],
                    'total_value'    => $validated['totalvalue'],
                    'hsn'       => $validated['hsncode'],
                    'description'    => $validated['description'],
                    'unit'           => $validated['unit'],
                    'received_date'  => $validated['receiveddate'],
                ]);

                return redirect()->route('inventory.index', [
                    'project_id' => $request->input('project_id'),
                    'store_id' => $request->input('store_id'),
                ])
                    ->with('success', 'Inventory (Street Light) added successfully.');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['error' => $e->getMessage()])
                    ->withInput();
            }
        } else {
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

                return redirect()->route('inventory.index')

                    ->with('success', 'Inventory created successfully.');
            } catch (\Exception $e) {
                // Catch database or other errors
                $errorMessage = $e->getMessage();

                return redirect()->back()
                    ->withErrors(['error' => $errorMessage])
                    ->withInput();
            }
        }
    }


    /**
     * Display the specified resource.
     */


    public function distinctInventoryStreetlight()
    {
        $distinctItems = InventroyStreetlightModel::select('item_code', 'item', 'total_quantity', 'rate', 'make', 'model')
            ->groupBy('item_code')
            ->get();
        return view('projects.project_inventory', compact('distinctItems'));
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
            // $storeName = "";

            // Fetch the project to determine the type
            $project = Project::findOrFail($projectId);
            $projectType = $project->project_type;

            // Determine the model to query based on project type
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;


            // Query inventory based on store_name and store_id
            $inventory = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId) // Filter by store_id directly
                ->with('dispatch')
                ->get();
            // Dispatch Inventory
            $dispatch = InventoryDispatch::where('isDispatched', true)
                ->where('store_id', $storeId)
                ->get();



            // Battery Data
            $totalBattery = $inventory->where('item_code', 'SL03')
                ->count();
            $batteryRate = $inventory->where('item_code', 'SL03')
                ->value('rate');
            $totalBatteryValue = $batteryRate * $totalBattery;
            $totalBatteryValue = number_format($totalBatteryValue, 2);
            // Battery Dispatch data
            $batteryDispatch = $dispatch->where('item_code', 'SL03')->count();
            $availableBattery = $totalBattery - $batteryDispatch;

            $dispatchAmountBattery = $dispatch->where('item_code', 'SL03')->sum('total_value');
            $dispatchAmountBattery = number_format($dispatchAmountBattery, 2);

            // Luminary Data
            $totalLuminary = $inventory->where('item_code', 'SL02')->count();
            $LuminaryRate = $inventory->where('item_code', 'SL02')
                ->value('rate');
            $totalLuminaryValue = $LuminaryRate * $totalLuminary;
            $totalLuminaryValue = number_format($totalLuminaryValue, 2);
            // Luminary Dispatch data
            $luminaryDispatch = $dispatch->where('item_code', 'SL02')->count();
            $availableLuminary = $totalLuminary - $luminaryDispatch;

            $dispatchAmountLuminary = $dispatch->where('item_code', 'SL02')->sum('total_value');
            $dispatchAmountLuminary = number_format($dispatchAmountLuminary, 2);

            //Structure Data
            // $totalStructure = $inventory->where('item_code', 'SL04')->count();
            // $StructureRate = $inventory->where('item_code', 'SL04')
            // ->value('rate');
            // $totalStructureValue = $StructureRate * $totalStructure;
            // $totalStructureValue = number_format($totalStructureValue, 2);
            // Structure Dispatch data
            // $structureDispatch = $dispatch->where('item_code', 'SL04')->count();
            // $availableStructure = $totalStructure - $structureDispatch;
            // $dispatchAmountStructure = $dispatch->where('item_code', 'SL04')->sum('total_value');
            // $dispatchAmountStructure = number_format($dispatchAmountStructure, 2);

            // Linking Battery to Structure
            $totalStructure = $totalBattery;
            $totalStructureValue = $totalBattery * 400;
            $structureDispatch = $batteryDispatch;
            $availableStructure = $availableBattery;

            // Module Data
            $totalModule = $inventory->where('item_code', 'SL01')->count();
            $ModuleRate = $inventory->where('item_code', 'SL01')
                ->value('rate');
            $totalModuleValue = $ModuleRate * $totalModule;
            $totalModuleValue = number_format($totalModuleValue, 2);
            // Module Dispatch data
            $moduleDispatch = $dispatch->where('item_code', 'SL01')->count();
            $availableModule = $totalModule - $moduleDispatch;

            $dispatchAmountModule = $dispatch->where('item_code', 'SL01')->sum('total_value');
            $dispatchAmountModule = number_format($dispatchAmountModule, 2);



            return view('inventory.view', compact(
                'inventory',
                'projectId',
                'storeName',
                'inchargeName',
                'projectType',
                'totalBattery',
                'totalBatteryValue',
                'batteryDispatch',
                'availableBattery',
                'dispatchAmountBattery',
                'totalStructure',
                'totalStructureValue',
                'structureDispatch',
                'availableStructure',
                'totalModule',
                'totalModuleValue',
                'availableModule',
                'moduleDispatch',
                'dispatchAmountModule',
                'totalLuminary',
                'totalLuminaryValue',
                'luminaryDispatch',
                'dispatchAmountLuminary',
                'availableLuminary',
            ));
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

            foreach ($request->serial_numbers as $serialNumber) {
                // Find the specific inventory item with this serial number
                $inventoryItem = $inventoryModel::where('serial_number', $serialNumber)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->first();
                // TODO: also check quantity is greater than 0

                if (!$inventoryItem) {
                    return redirect()->back()->with('error', "Item with serial number {$serialNumber} not found or already dispatched");
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
                    'total_quantity' => "1",
                    'total_value' => $request->rate,
                    'serial_number' => $serialNumber,
                    'dispatch_date' => Carbon::now(),
                    "isDispatched" => true

                ]);
                Log::info("Dispatching item");
                Log::info($dispatch);

                // Reduce stock from inventory
                $inventoryItem->decrement('quantity', 1);
                $dispatchedItems[] = $dispatch;
            }
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventory dispatched successfully'
                ]);
            }
            return redirect()->back()->with('success', 'Inventory dispatched successfully');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Inventory dispatched Failed');
        }
    }


    public function viewVendorInventory($vendorId)
    {
        Log::info("Fetching inventory for vendor_id: {$vendorId}");

        try {
            $todayDate = now()->toDateString(); // Get today's date

            // Fetch inventory dispatched to the vendor
            $inventory = InventoryDispatch::where('vendor_id', $vendorId)
                ->with(['project', 'store', 'storeIncharge'])
                ->get();

            if ($inventory->isEmpty()) {
                Log::warning("No inventory found for vendor_id: {$vendorId}");
                return response()->json([
                    'message' => 'No inventory found for this vendor.',
                    'vendor_id' => $vendorId
                ], 404);
            }

            // Filter today's inventory
            $todayInventory = $inventory->where('dispatch_date', '>=', $todayDate)
                ->groupBy('item_code')->map(function ($items) {
                    return $this->formatInventoryItem($items);
                })->values();

            // Group inventory by item_code
            $groupedInventory = $inventory->groupBy('item_code')->map(function ($items) {
                return $this->formatInventoryItem($items);
            });

            // Split into categories based on `is_consumed`
            $totalReceived = [];
            $inStock = [];
            $consumed = [];

            foreach ($groupedInventory as $item) {
                $matchingItems = $inventory->where('item_code', $item['item_code']);

                $consumedItems = $matchingItems->where('is_consumed', 1);
                $inStockItems = $matchingItems->where('is_consumed', 0);

                // If any item is consumed, move to "consumed"
                if ($consumedItems->isNotEmpty()) {
                    $consumed[] = $this->formatInventoryItem($consumedItems);
                }

                // If any item is still in stock, move to "in_stock"
                if ($inStockItems->isNotEmpty()) {
                    $inStock[] = $this->formatInventoryItem($inStockItems);
                }

                // Add all items to total received
                $totalReceived[] = $item;
            }

            // Prepare final response
            $response = [
                'vendor_id' => $vendorId,
                'project_id' => optional($inventory->first()->project)->id,
                'project_name' => optional($inventory->first()->project)->project_name,
                'total_inventory_value' => number_format($inventory->sum('total_value'), 2, '.', ''),
                'inventory_count' => count($groupedInventory),
                'today_inventory' => $todayInventory,
                'all_inventory' => [
                    'total_received' => $totalReceived,
                    'in_stock' => $inStock,
                    'consumed' => $consumed,
                ],
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

    /**
     * Helper function to format inventory items
     */
    private function formatInventoryItem($items)
    {
        $firstItem = $items->first();
        return [
            'item_code' => $firstItem->item_code,
            'item' => $firstItem->item,
            'manufacturer' => $firstItem->manufacturer,
            'make' => $firstItem->make,
            'model' => $firstItem->model,
            'rate' => (float)$firstItem->rate,
            'total_quantity' => $items->sum('total_quantity'),
            'total_value' => $items->sum('total_value'),
            'dispatch_date' => $firstItem->dispatch_date,
            'serial_number' => $items->pluck('serial_number')->flatten()->filter()->values()->all(),
            'store_name' => optional($firstItem->store)->store_name,
            'store_incharge' => optional($firstItem->storeIncharge)->firstName . ' ' .
                optional($firstItem->storeIncharge)->lastName,
        ];
    }


    public function checkQR(Request $request)
    {
        try {
            Log::info($request->all());
            $exists = InventroyStreetLightModel::where('serial_number', $request->qr_code)
                ->where('store_id', $request->store_id) // Ensure it belongs to the same store
                ->where('item_code', $request->item_code) // Ensure it belongs to the same item code
                ->where('quantity', '>', 0) // Ensure quantity is greater than 0
                ->exists();
            return response()->json(['exists' => $exists]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    // TODO: Streetlight show dispatch inventory code here
    public function showDispatchInventory(Request $request)
    {
        $itemCode = $request->item_code;
        $storeid = $request->store_id;
        try {
            //code...
            $item = InventroyStreetLightModel::where('item_code', $itemCode)
                ->where('store_id', $storeid)
                ->get();
            $specificDispatch = InventoryDispatch::where('item_code', $itemCode)
                ->where('store_id', $storeid)->get();
            $availableQuantity = 0;
            $title = $itemCode;
            // print_r($dispatchedItem->toArray());
            return view('inventory.dispatchedStock', compact('specificDispatch', 'availableQuantity', 'title'));
        } catch (\Exception $e) {
            //throw $th;
            echo ($e->getMessage());
        }
    }

    public function returnInventory(Request $request)
    {
        $serial_number = $request->input('serial_number');
        Log::info('Return inventory', ['serial_number' => $serial_number]);
        try {
            $inventory = InventroyStreetLightModel::where('serial_number', $serial_number)->first();
            $dispatch = InventoryDispatch::where('serial_number', $serial_number)->whereNull('streetlight_pole_id')->first();
            if (!$inventory) {
                Log::warning('Inventory not found for serial_number', ['serial_number' => $serial_number]);
                return redirect()->back()->with('error', 'Inventory item not found.');
            }
            $inventory->quantity = 1;
            $inventory->save();
            // Find and delete the corresponding dispatch record by serial number

            if ($dispatch) {
                $dispatch->delete();
            }

            return redirect()->back()->with('success', 'Inventory item returned successfully');
        } catch (\Exception $e) {
            Log::error('Failed to return inventory item', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to return inventory item');
        }
    }
}

<?php

namespace App\Http\Controllers;

use Exception;
use App\Imports\InventoryImport;
use App\Imports\InventroyStreetLight;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
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

    public function viewInventory(Request $request)
    {
        try {
            $projectId = $request->project_id;
            $storeId = $request->store_id;

            // 1. Fetch store and project
            $store = Stores::with('user')->findOrFail($storeId);
            $project = Project::findOrFail($projectId);

            $storeName = $store->store_name;
            $inchargeName = $store->storeIncharge ? $store->storeIncharge->firstName . ' ' . $store->storeIncharge->lastName : 'N/A';
            $projectType = $project->project_type;

            // 2. Determine model
            $inventoryModel = ($projectType == 1) ? InventroyStreetLightModel::class : Inventory::class;

            // 3. Inventory stats
            $inventoryStats = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->select(
                    'item_code',
                    DB::raw('COUNT(*) as total_items'),
                    DB::raw('MAX(rate) as item_rate')
                )
                ->groupBy('item_code')
                ->get()
                ->keyBy('item_code');

            // 4. Dispatch stats
            $dispatchStats = InventoryDispatch::where('isDispatched', true)
                ->where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->select(
                    'item_code',
                    DB::raw('COUNT(*) as dispatched_items'),
                    DB::raw('SUM(total_value) as dispatched_value')
                )
                ->groupBy('item_code')
                ->get()
                ->keyBy('item_code');

            // 5. Paginate inventory (prevent memory leak here)
            $inventory = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->with('dispatch')
<<<<<<< HEAD
                ->paginate(10000); // Load 50 items per page
=======
                ->get();
>>>>>>> f825c9f0b561ec0ffb351e6bd76c219dd5835433

            // 6. Calculate derived totals
            $totalBattery = $inventoryStats->get('SL03')->total_items ?? 0;
            $batteryRate = $inventoryStats->get('SL03')->item_rate ?? 0;
            $totalBatteryValue = $batteryRate * $totalBattery;
            $batteryDispatch = $dispatchStats->get('SL03')->dispatched_items ?? 0;
            $dispatchAmountBattery = $dispatchStats->get('SL03')->dispatched_value ?? 0;
            $availableBattery = $totalBattery - $batteryDispatch;

            $totalLuminary = $inventoryStats->get('SL02')->total_items ?? 0;
            $luminaryRate = $inventoryStats->get('SL02')->item_rate ?? 0;
            $totalLuminaryValue = $luminaryRate * $totalLuminary;
            $luminaryDispatch = $dispatchStats->get('SL02')->dispatched_items ?? 0;
            $dispatchAmountLuminary = $dispatchStats->get('SL02')->dispatched_value ?? 0;
            $availableLuminary = $totalLuminary - $luminaryDispatch;

            $totalModule = $inventoryStats->get('SL01')->total_items ?? 0;
            $moduleRate = $inventoryStats->get('SL01')->item_rate ?? 0;
            $totalModuleValue = $moduleRate * $totalModule;
            $moduleDispatch = $dispatchStats->get('SL01')->dispatched_items ?? 0;
            $dispatchAmountModule = $dispatchStats->get('SL01')->dispatched_value ?? 0;
            $availableModule = $totalModule - $moduleDispatch;

            $totalStructure = $totalBattery;
            $totalStructureValue = $totalBattery * 400;
            $structureDispatch = $batteryDispatch;
            $availableStructure = $availableBattery;

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
                'totalLuminary',
                'totalLuminaryValue',
                'luminaryDispatch',
                'availableLuminary',
                'totalModule',
                'totalModuleValue',
                'moduleDispatch',
                'availableModule',
                'dispatchAmountModule',
                'totalStructure',
                'totalStructureValue',
                'structureDispatch',
                'availableStructure'
            ));
        } catch (Exception $e) {
            Log::error("Error in viewInventory: " . $e->getMessage());
            return back()->with('error', 'Could not load inventory data. Please try again.');
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


    public function viewVendorInventory(Request $request, $vendorId)
{
    Log::info("Fetching inventory for vendor_id: {$vendorId}");

    try {
        $todayDate = now()->toDateString();

        // Set the page size, default to 100 if not provided
        $perPage = $request->get('per_page', 100);

        // Fetch paginated inventory
        $inventoryPaginated = InventoryDispatch::where('vendor_id', $vendorId)
            ->with(['project', 'store', 'storeIncharge'])
            ->orderBy('dispatch_date', 'desc') // Optional: keep it ordered
            ->paginate($perPage);

        $inventory = collect($inventoryPaginated->items());

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

            if ($consumedItems->isNotEmpty()) {
                $consumed[] = $this->formatInventoryItem($consumedItems);
            }

            if ($inStockItems->isNotEmpty()) {
                $inStock[] = $this->formatInventoryItem($inStockItems);
            }

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
            'pagination' => [
                'total' => $inventoryPaginated->total(),
                'per_page' => $inventoryPaginated->perPage(),
                'current_page' => $inventoryPaginated->currentPage(),
                'last_page' => $inventoryPaginated->lastPage(),
                'next_page_url' => $inventoryPaginated->nextPageUrl(),
                'prev_page_url' => $inventoryPaginated->previousPageUrl(),
            ]
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

    public function replaceItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'old_serial_number' => 'required',
            'new_serial_number' => 'required|string',
            'authentication_code' => 'required|string',
            'agreement_checkbox' => 'required|accepted',
        ]);
        // Step 0: Check Authentication
	Log::info(env('REPLACEMENT_AUTH_CODE'));
        if ($request->authentication_code !== env('REPLACEMENT_AUTH_CODE')) {
            return back()->withInput()->with('replace_error', 'Invalid authentication code.');
        }
        DB::beginTransaction();

        try {

            $oldDispatch = InventoryDispatch::findOrFail($request->item_id);
            $newSerial = $request->new_serial_number;

            // ---------- Step 1: Handle inventory_dispatch ----------
            $newDispatch = InventoryDispatch::where('serial_number', $newSerial)->first();

            if (!$newDispatch) {
                // Clone from old
                $newDispatch = $oldDispatch->replicate();
                $newDispatch->serial_number = $newSerial;
                $newDispatch->save();
            } else {
                // Update newDispatch with oldDispatch values (except ID & serial_number)
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
                    'project_id',
                    'streetlight_pole_id'
                ]));
                $newDispatch->save();
            }

            // ---------- Step 2: Handle inventory_streetlight ----------
            $newStreet = InventroyStreetLightModel::where('serial_number', $newSerial)->first();

            if ($newStreet) {
                $newStreet->quantity = 0;
                $newStreet->save();
            } else {
                $oldStreet = InventroyStreetLightModel::where('serial_number', $oldDispatch->serial_number)->first();
                if ($oldStreet) {
                    $newStreet = $oldStreet->replicate();
                    $newStreet->serial_number = $newSerial;
                    $newStreet->quantity = 0;
                    $newStreet->save();
                }
            }

            // ---------- Step 3: Delete old item from dispatch ----------
            $oldDispatch->delete();

            // ---------- Step 4: Update quantity of old item in streetlight ----------
            if (isset($oldStreet)) {
                $oldStreet->quantity = 1;
                $oldStreet->save();
            }

            // ---------- Step 5: Update pole columns ----------
            $pole = Pole::find($newDispatch->streetlight_pole_id);

            if ($pole) {
                switch ($newDispatch->item_code) {
                    case 'SL01':
                        $pole->panel_qr = $newSerial;
                        break;
                    case 'SL02':
                        $pole->luminary_qr = $newSerial;
                        break;
                    case 'SL03':
                        $pole->battery_qr = $newSerial;
                        break;
                }
                $pole->save();
            }
            DB::commit();
            return back()->with('success', 'Item replaced successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return back()->withInput()->with('replace_error', 'Failed to replace item: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);

            if (!is_array($ids) || empty($ids)) {
                return redirect()->back()->with('error', 'No valid items selected for deletion.');
            }

            InventroyStreetLightModel::whereIn('id', $ids)->delete();

            return redirect()->back()->with('success', 'Selected inventory items deleted successfully.');
        } catch (\Throwable $th) {
            \Log::error("Bulk delete error: " . $th->getMessage());
            return redirect()->back()->with('error', 'An error occurred while deleting inventory items.');
        }
    }
}

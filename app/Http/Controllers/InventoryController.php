<?php

namespace App\Http\Controllers;

use App\Contracts\Services\Inventory\InventoryServiceInterface;
use App\Exports\InventoryImportFormatExport;
use App\Imports\InventoryDispatchImport;
use App\Imports\InventoryImport;
use App\Imports\InventroyStreetLight;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Inventory\InventoryHistoryService;
class InventoryController extends Controller
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService,
        protected InventoryHistoryService $historyService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $projectId = $request->query('project_id');

        if (!$projectId && $user->project_id) {
            $projectId = $user->project_id;
        }
        if (!$projectId) {
            $project = Project::when($user->role !== \App\Enums\UserRole::ADMIN->value, function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->first();
            $projectId = $project ? $project->id : null;
        }

        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'No project assigned. Please select a project to view inventory.');
        }

        // For Project Managers, ensure they can only see projects they're assigned to
        if ($user->role === \App\Enums\UserRole::PROJECT_MANAGER->value) {
            $isAssigned = DB::table('project_user')
                ->where('project_id', $projectId)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isAssigned) {
                return redirect()->route('projects.index')->with('error', 'You do not have access to this project.');
            }
        }

        $storeId = $request->query('store_id');

        // Get all projects for Admin (for sidebar project selector)
        $allProjects = [];
        if ($user->role === \App\Enums\UserRole::ADMIN->value) {
            $allProjects = Project::orderBy('project_name')->get();
        } elseif ($user->role === \App\Enums\UserRole::PROJECT_MANAGER->value) {
            // PMs see only their assigned projects
            $allProjects = Project::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->orderBy('project_name')->get();
        }

        if ($projectId && $storeId) {
            $inventory = InventroyStreetLightModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->paginate(50);
        } else {
            $inventory = InventroyStreetLightModel::where('project_id', $projectId)
                ->paginate(50);
        }

        // Get stores for the selected project
        $stores = Stores::where('project_id', $projectId)->get();
        $selectedProject = Project::find($projectId);

        return view('inventory.index', compact('inventory', 'allProjects', 'selectedProject', 'stores', 'projectId', 'storeId'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        $projectId = $request->projectId;
        $storeId = $request->storeId;
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
        $storeId = $request->storeId;
        try {
            $import = new InventroyStreetLight($projectId, $storeId);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $importedCount = $import->getImportedCount();
            $errorFileUrl = null;

            if (!empty($errors)) {
                // Build a downloadable errors.txt file
                $lines = [];
                $lines[] = 'Inventory Import Errors - ' . now()->toDateTimeString();
                $lines[] = 'Project ID: ' . $projectId . ', Store ID: ' . $storeId;
                $lines[] = str_repeat('=', 80);

                foreach ($errors as $err) {
                    $reason = $err['reason'] ?? 'unknown';
                    $itemCode = $err['item_code'] ?? '';
                    $serial = $err['serial_number'] ?? '';
                    $item = $err['item'] ?? '';
                    $sim = $err['sim_number'] ?? '';

                    $lines[] = "Reason: {$reason}";
                    $lines[] = "  Item Code: {$itemCode}";
                    $lines[] = "  Item: {$item}";
                    $lines[] = "  Serial: {$serial}";
                    if ($sim) {
                        $lines[] = "  SIM: {$sim}";
                    }
                    $lines[] = str_repeat('-', 40);
                }

                $content = implode(PHP_EOL, $lines) . PHP_EOL;

                // Use the public disk so URL generation is reliable
                $disk = Storage::disk('public');
                if (!$disk->exists('import_errors')) {
                    $disk->makeDirectory('import_errors');
                }

                $fileName = 'inventory_errors_project_' . $projectId . '_store_' . $storeId . '_' . time() . '.txt';
                $relativePath = 'import_errors/' . $fileName;
                $disk->put($relativePath, $content);

                // Public URL (requires `php artisan storage:link` once)
                $errorFileUrl = $disk->url($relativePath);
            }

            // On success, stay on store page and open View Inventory tab
            $redirect = redirect()->to(route('store.show', $storeId) . '#view')
                ->with('import_errors_url', $errorFileUrl)
                ->with('import_errors_count', count($errors));

            if ($importedCount > 0) {
                $message = "Inventory imported successfully! Imported rows: {$importedCount}";
                if (!empty($errors)) {
                    $message .= ', Skipped rows: ' . count($errors);
                }
                $redirect->with('success', $message);
            } else {
                // No new rows imported - treat as warning/error
                $message = 'No new inventory imported. Skipped rows: ' . count($errors);
                $redirect->withErrors(['error' => $message]);
            }

            return $redirect;
        } catch (\Exception $e) {
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
        try {
            $projectType = (int) $request->project_type;
            $itemCode = $request->input('code') ?? $request->input('item_code');
            $serialNumber = $request->input('serialnumber') ?? $request->input('serial_number');

            // Validate streetlight item code restrictions
            if ($projectType == 1) {
                $validItemCodes = ['SL01', 'SL02', 'SL03', 'SL04'];
                if (!in_array($itemCode, $validItemCodes)) {
                    return redirect()->back()
                        ->withErrors(['code' => 'Invalid item code for streetlight project. Allowed codes: SL01 (Panel), SL02 (Luminary), SL03 (Battery), SL04 (Structure).'])
                        ->withInput();
                }
            }

            // Validate serial_number uniqueness
            if ($projectType == 1 && $serialNumber) {
                $existingSerial = InventroyStreetLightModel::where('serial_number', $serialNumber)
                    ->exists();

                if ($existingSerial) {
                    return redirect()->back()
                        ->withErrors(['serialnumber' => 'This serial number is already in use.'])
                        ->withInput();
                }
            }

            // Validate sim_number uniqueness for luminary items (SL02) only
            if ($projectType == 1 && $itemCode === 'SL02' && $request->filled('sim_number')) {
                $existingSim = InventroyStreetLightModel::where('sim_number', $request->sim_number)
                    ->where('item_code', 'SL02')
                    ->exists();

                if ($existingSim) {
                    return redirect()->back()
                        ->withErrors(['sim_number' => 'This SIM number is already in use for a luminary item.'])
                        ->withInput();
                }
            }

            $inventory = $this->inventoryService->addInventoryItem(
                $request->all(),
                $projectType
            );

            // If called from a store context, stay on the store page and switch to View Inventory tab
            $storeId = $request->input('store_id');
            if ($storeId) {
                return redirect()
                    ->to(route('store.show', $storeId) . '#view')
                    ->with('success', 'Inventory added successfully.');
            }

            // Fallback: redirect to inventory index (legacy behavior)
            return redirect()->route('inventory.index', [
                'project_id' => $request->input('project_id'),
                'store_id' => $storeId,
            ])->with('success', 'Inventory added successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating inventory: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Check if a serial number already exists (AJAX real-time validation).
     */
    public function checkSerial(Request $request)
    {
        try {
            $projectType = (int) $request->input('project_type', 1);
            $serialNumber = $request->input('serialnumber') ?? $request->input('serial_number');

            if (!$serialNumber) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Serial number is required.',
                ]);
            }

            // For streetlight inventory, check InventroyStreetLightModel
            if ($projectType === 1) {
                $query = InventroyStreetLightModel::where('serial_number', $serialNumber);

                if ($request->filled('project_id')) {
                    $query->where('project_id', $request->input('project_id'));
                }

                if ($request->filled('store_id')) {
                    $query->where('store_id', $request->input('store_id'));
                }

                $exists = $query->exists();

                return response()->json([
                    'exists' => $exists,
                    'message' => $exists ? 'This serial number is already in use.' : 'Serial number is available.',
                ]);
            }

            // Fallback for other project types (not used in current flow)
            return response()->json([
                'exists' => false,
                'message' => 'Serial number is available.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking serial number: ' . $e->getMessage());
            return response()->json([
                'exists' => false,
                'message' => 'Unable to validate serial number.',
            ], 500);
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

    /**
     * Show the form for editing the specified resource.
     */
    public function editInventory($id)
    {
        $inventoryItem = Inventory::findOrFail($id);
        return view('inventory.editInventory', compact('inventoryItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateInventory(Request $request, $id)
    {
        $validated = $request->validate([
            'productName' => 'required|string|max:255',
            'brand' => 'nullable|string',
            'description' => 'nullable|string',
            'initialQuantity' => 'required|string',
            'quantityStock' => 'nullable|string',
            'unit' => 'required|string|max:25',
            'receivedDate' => 'nullable|date',
        ]);

        try {
            $inventoryItem = Inventory::findOrFail($id);
            $inventoryItem->update($validated);

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory updated successfully.');
        } catch (\Exception $e) {
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
            'productName' => 'required|string|max:255',
            'brand' => 'nullable|string',
            'description' => 'nullable|string',
            'initialQuantity' => 'required|string',
            'quantityStock' => 'nullable|string',
            'unit' => 'required|string|max:25',
            'receivedDate' => 'nullable|date',
        ]);

        try {

            $item->update($validated);
            return redirect()->route('inventory.show', compact('item'))
                ->with('success', 'Inventory updated successfully.');
        } catch (\Exception $e) {
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
        try {
            // Try to find in both inventory models
            $item = InventroyStreetLightModel::find($id);
            if (!$item) {
                $item = Inventory::findOrFail($id);
            }
            $item->delete();
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
            $store = Stores::findOrFail($storeId);
            $storeName = $store->store_name;
            $storeIncharge = User::findOrFail($store->store_incharge_id);
            $inchargeName = $storeIncharge->firstName . ' ' . $storeIncharge->lastName;

            $project = Project::findOrFail($projectId);
            $projectType = $project->project_type;
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

            // Get all inventory items (no pagination for unified view)
            $allInventory = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get all dispatched items for this store
            $allDispatched = InventoryDispatch::where('isDispatched', true)
                ->where('store_id', $storeId)
                ->where('project_id', $projectId)
                ->with('vendor')
                ->get()
                ->keyBy('serial_number');

            // Build unified inventory array with availability status
            $unifiedInventory = [];
            foreach ($allInventory as $item) {
                $dispatch = $allDispatched->get($item->serial_number);
                $quantity = $item->quantity ?? 0;

                // Determine availability status based on quantity and dispatch
                $availability = 'In Stock';
                $vendorId = null;
                $vendorName = null;
                $dispatchDate = null;
                $dispatchId = null;

                // If quantity > 0, item is in stock (regardless of dispatch records)
                // If quantity = 0, check dispatch status
                if ($quantity > 0) {
                    $availability = 'In Stock';
                } elseif ($dispatch) {
                    $dispatchId = $dispatch->id;
                    $dispatchDate = $dispatch->dispatch_date;
                    $vendorId = $dispatch->vendor_id;
                    $vendorName = $dispatch->vendor ? ($dispatch->vendor->firstName . ' ' . $dispatch->vendor->lastName) : null;

                    if ($dispatch->is_consumed == 1) {
                        $availability = 'Consumed';
                    } else {
                        $availability = 'Dispatched';
                    }
                }

                $unifiedInventory[] = [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item' => $item->item,
                    'manufacturer' => $item->manufacturer ?? $item->make ?? '',
                    'model' => $item->model ?? '',
                    'serial_number' => $item->serial_number,
                    'hsn' => $item->hsn ?? '',
                    'created_at' => $item->created_at,
                    'quantity' => $item->quantity ?? 1,
                    'rate' => $item->rate ?? 0,
                    'availability' => $availability,
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendorName,
                    'dispatch_date' => $dispatchDate,
                    'dispatch_id' => $dispatchId,
                ];
            }

            // For backward compatibility, keep old paginated query (will be ignored in view)
            $inventory = collect($unifiedInventory);

            $dispatch = InventoryDispatch::where('isDispatched', true)
                ->where('store_id', $storeId)
                ->get();
            $batteryQuery = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->where('item_code', 'SL03');
            $totalBattery = $batteryQuery->count();
            $batteryRate = $batteryQuery->value('rate') ?? 0;
            $totalBatteryValue = $batteryQuery->sum(DB::raw('quantity * rate'));
            $totalBatteryValue = number_format($totalBatteryValue, 2);
            // Battery Dispatch data
            $batteryDispatch = $dispatch->where('item_code', 'SL03')->count();
            $availableBattery = $totalBattery - $batteryDispatch;
            $dispatchAmountBattery = $dispatch->where('item_code', 'SL03')->sum('total_value');
            $dispatchAmountBattery = number_format($dispatchAmountBattery, 2);

            // Luminary Data
            $luminaryQuery = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->where('item_code', 'SL02');
            $totalLuminary = $luminaryQuery->count();
            $LuminaryRate = $luminaryQuery->value('rate') ?? 0;
            $totalLuminaryValue = $luminaryQuery->sum(DB::raw('quantity * rate'));
            $totalLuminaryValue = number_format($totalLuminaryValue, 2);
            // Luminary Dispatch data
            $luminaryDispatch = $dispatch->where('item_code', 'SL02')->count();
            $availableLuminary = $totalLuminary - $luminaryDispatch;
            $dispatchAmountLuminary = $dispatch->where('item_code', 'SL02')->sum('total_value');
            $dispatchAmountLuminary = number_format($dispatchAmountLuminary, 2);

            // Linking Battery to Structure
            $totalStructure = $totalBattery;
            $totalStructureValue = $totalBattery * 400;
            $structureDispatch = $batteryDispatch;
            $availableStructure = $availableBattery;

            // Module Data
            $moduleQuery = $inventoryModel::where('project_id', $projectId)
                ->where('store_id', $storeId)
                ->where('item_code', 'SL01');
            $totalModule = $moduleQuery->count();
            $ModuleRate = $moduleQuery->value('rate') ?? 0;
            $totalModuleValue = $moduleQuery->sum(DB::raw('quantity * rate'));
            $totalModuleValue = number_format($totalModuleValue, 2);
            // Module Dispatch data
            $moduleDispatch = $dispatch->where('item_code', 'SL01')->count();
            $availableModule = $totalModule - $moduleDispatch;
            $dispatchAmountModule = $dispatch->where('item_code', 'SL01')->sum('total_value');
            $dispatchAmountModule = number_format($dispatchAmountModule, 2);



            // Get distinct item codes for filter
            $itemCodes = $allInventory->pluck('item_code')->unique()->sort()->values();

            return view('inventory.view', compact(
                'inventory',
                'unifiedInventory',
                'itemCodes',
                'projectId',
                'storeId',
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
                    ->where('quantity', '>', 0)
                    ->first();

                if (!$inventoryItem) {
                    return redirect()->back()->with('error', "Item with serial number {$serialNumber} not found or already dispatched");
                }
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
                $inventoryItem->decrement('quantity', 1);

                // Log history
                $inventoryType = ($project->project_type == 1) ? 'streetlight' : 'rooftop';
                $this->historyService->logDispatched($dispatch, $inventoryItem, $inventoryType);

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

    /**
     * Bulk dispatch inventory from Excel file
     */
    public function bulkDispatchFromExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048',
                'vendor_id' => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
                'store_id' => 'required|exists:stores,id',
                'store_incharge_id' => 'required|exists:users,id',
            ]);

            $project = Project::findOrFail($request->project_id);
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

            // Import Excel data
            $import = new InventoryDispatchImport($request->project_id, $request->store_id);
            $data = Excel::toCollection($import, $request->file('file'))->first();

            $validItems = [];
            $alreadyDispatched = [];
            $duplicateSerials = [];
            $nonExisting = [];
            
            // Track serial numbers seen in uploaded file for duplicate detection
            $seenSerials = [];

            // First pass: Detect duplicates within the uploaded file
            foreach ($data as $row) {
                $serialNumber = $row['serial_number'] ?? $row['SERIAL_NUMBER'] ?? null;
                if ($serialNumber) {
                    if (isset($seenSerials[$serialNumber])) {
                        $seenSerials[$serialNumber]++;
                    } else {
                        $seenSerials[$serialNumber] = 1;
                    }
                }
            }

            // Second pass: Categorize items
            foreach ($data as $row) {
                $itemCode = $row['item_code'] ?? $row['ITEM_CODE'] ?? null;
                $itemName = $row['item'] ?? $row['ITEM NAME'] ?? $row['item_name'] ?? null;
                $serialNumber = $row['serial_number'] ?? $row['SERIAL_NUMBER'] ?? null;
                $simNumber = ($itemCode === 'SL02') ? ($row['sim_number'] ?? $row['SIM_NUMBER'] ?? null) : null;

                if (!$itemCode || !$itemName || !$serialNumber) {
                    $nonExisting[] = [
                        'item_code' => $itemCode ?? 'N/A',
                        'item' => $itemName ?? 'N/A',
                        'serial_number' => $serialNumber ?? 'N/A',
                        'sim_number' => $simNumber,
                        'reason' => 'Missing required fields: item_code, item, or serial_number'
                    ];
                    continue;
                }

                // Check for duplicates within uploaded file
                if (isset($seenSerials[$serialNumber]) && $seenSerials[$serialNumber] > 1) {
                    $duplicateSerials[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                    ];
                    continue;
                }

                // Check if serial number exists in inventory
                $inventoryItem = $inventoryModel::where('serial_number', $serialNumber)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->where('item_code', $itemCode)
                    ->first();

                if (!$inventoryItem) {
                    $nonExisting[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                        'reason' => "Serial number {$serialNumber} not found in inventory"
                    ];
                    continue;
                }

                // Check if quantity is 1
                if ($inventoryItem->quantity != 1) {
                    $nonExisting[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                        'reason' => "Serial number {$serialNumber} has quantity {$inventoryItem->quantity}, expected 1"
                    ];
                    continue;
                }

                // Check if already dispatched
                $existingDispatch = InventoryDispatch::where('serial_number', $serialNumber)
                    ->where('isDispatched', true)
                    ->exists();

                if ($existingDispatch) {
                    $alreadyDispatched[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                    ];
                    continue;
                }

                // Validate SIM number for luminary items
                if ($itemCode === 'SL02' && $simNumber) {
                    $existingSim = InventroyStreetLightModel::where('sim_number', $simNumber)
                        ->where('item_code', 'SL02')
                        ->where('id', '!=', $inventoryItem->id)
                        ->exists();

                    if ($existingSim) {
                        $nonExisting[] = [
                            'item_code' => $itemCode,
                            'item' => $itemName,
                            'serial_number' => $serialNumber,
                            'sim_number' => $simNumber,
                            'reason' => "SIM number {$simNumber} already exists for another luminary item"
                        ];
                        continue;
                    }
                }

                // Add to valid items (without inventory_item object, just data)
                $validItems[] = [
                    'item_code' => $itemCode,
                    'item' => $itemName,
                    'serial_number' => $serialNumber,
                    'sim_number' => $simNumber,
                    'rate' => $inventoryItem->rate,
                    'make' => $inventoryItem->make,
                    'model' => $inventoryItem->model,
                    'inventory_id' => $inventoryItem->id,
                ];
            }

            // Return preview only - never dispatch automatically
            return response()->json([
                'status' => 'preview',
                'valid_items' => $validItems,
                'already_dispatched' => $alreadyDispatched,
                'duplicate_serials' => $duplicateSerials,
                'non_existing' => $nonExisting,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk dispatch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process bulk dispatch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm and dispatch items from bulk upload preview
     */
    public function confirmBulkDispatch(Request $request)
    {
        try {
            $request->validate([
                'vendor_id' => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
                'store_id' => 'required|exists:stores,id',
                'store_incharge_id' => 'required|exists:users,id',
                'serial_numbers' => 'required|array',
                'serial_numbers.*' => 'required|string',
            ]);

            $project = Project::findOrFail($request->project_id);
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

            $serialNumbers = $request->serial_numbers;
            $dispatchedItems = [];
            $failedItems = [];

            DB::beginTransaction();

            try {
                foreach ($serialNumbers as $serialNumber) {
                    // Find the inventory item
                    $inventoryItem = $inventoryModel::where('serial_number', $serialNumber)
                        ->where('project_id', $request->project_id)
                        ->where('store_id', $request->store_id)
                        ->where('quantity', '>', 0)
                        ->first();

                    if (!$inventoryItem) {
                        $failedItems[] = [
                            'serial_number' => $serialNumber,
                            'error' => 'Item not found or out of stock'
                        ];
                        continue;
                    }

                    // Double-check if already dispatched (race condition protection)
                    $existingDispatch = InventoryDispatch::where('serial_number', $serialNumber)
                        ->where('isDispatched', true)
                        ->exists();

                    if ($existingDispatch) {
                        $failedItems[] = [
                            'serial_number' => $serialNumber,
                            'error' => 'Item already dispatched'
                        ];
                        continue;
                    }

                    // Create dispatch record
                    $dispatch = InventoryDispatch::create([
                        'vendor_id' => $request->vendor_id,
                        'project_id' => $request->project_id,
                        'store_id' => $request->store_id,
                        'store_incharge_id' => $request->store_incharge_id,
                        'item_code' => $inventoryItem->item_code,
                        'item' => $inventoryItem->item,
                        'rate' => $inventoryItem->rate,
                        'make' => $inventoryItem->make,
                        'model' => $inventoryItem->model,
                        'total_quantity' => 1,
                        'total_value' => $inventoryItem->rate,
                        'serial_number' => $serialNumber,
                        'dispatch_date' => Carbon::now(),
                        'isDispatched' => true,
                    ]);

                    // Update inventory quantity
                    $inventoryItem->decrement('quantity', 1);

                    // Log history
                    $inventoryType = ($project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logDispatched($dispatch, $inventoryItem, $inventoryType);

                    $dispatchedItems[] = $dispatch;
                }

                DB::commit();

                $message = "Successfully dispatched " . count($dispatchedItems) . " item(s)";
                if (count($failedItems) > 0) {
                    $message .= ". " . count($failedItems) . " item(s) failed to dispatch.";
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'dispatched_count' => count($dispatchedItems),
                    'failed_items' => $failedItems,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Confirm bulk dispatch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm bulk dispatch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewVendorInventory($vendorId)
    {
        try {
            $todayDate = now()->toDateString();
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

            $groupedInventory = $inventory->groupBy('item_code')->map(function ($items) {
                return $this->formatInventoryItem($items);
            });

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
            'rate' => (float) $firstItem->rate,
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
            $exists = InventroyStreetLightModel::where('serial_number', $request->qr_code)
                ->where('store_id', $request->store_id)
                ->where('item_code', $request->item_code)
                ->where('quantity', '>', 0)
                ->exists();
            return response()->json(['exists' => $exists]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function showDispatchInventory(Request $request)
    {
        $itemCode = $request->item_code;
        $storeid = $request->store_id;
        try {
            $item = InventroyStreetLightModel::where('item_code', $itemCode)
                ->where('store_id', $storeid)
                ->get();
            $specificDispatch = InventoryDispatch::where('item_code', $itemCode)
                ->where('store_id', $storeid)->get();
            $availableQuantity = 0;
            $title = $itemCode;
            return view('inventory.dispatchedStock', compact('specificDispatch', 'availableQuantity', 'title'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function returnInventory(Request $request)
    {
        $serial_number = $request->input('serial_number');
        try {
            $inventory = InventroyStreetLightModel::where('serial_number', $serial_number)->first();
            $dispatch = InventoryDispatch::where('serial_number', $serial_number)->whereNull('streetlight_pole_id')->first();
            if (!$inventory) {
                Log::warning('Inventory not found for serial_number', ['serial_number' => $serial_number]);
                return redirect()->back()->with('error', 'Inventory item not found.');
            }
            $quantityBefore = $inventory->quantity;
            $inventory->quantity = 1;
            $inventory->save();

            // Log history
            $project = Project::find($inventory->project_id);
            $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
            $this->historyService->logReturned(
                $inventory,
                $inventoryType,
                $inventory->project_id,
                $inventory->store_id,
                1
            );

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

            // Get pole if available for history
            $pole = \App\Models\Pole::find($newDispatch->streetlight_pole_id ?? $oldDispatch->streetlight_pole_id);

            // Log history before deleting old dispatch
            $project = Project::find($oldDispatch->project_id);
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

            $oldDispatch->delete();

            if (isset($oldStreet)) {
                $oldStreet->quantity = 1;
                $oldStreet->save();
            }

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
            return redirect()->back()->with('error', 'An error occurred while deleting inventory items.');
        }
    }

    public function bulkReturn(Request $request)
    {
        try {
            $serialNumbers = $request->input('serial_numbers', []);
            
            if (!is_array($serialNumbers) || empty($serialNumbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid items selected for return.'
                ], 400);
            }

            $returnedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($serialNumbers as $serialNumber) {
                try {
                    $inventory = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();
                    
                    if (!$inventory) {
                        $errors[] = "Inventory item not found for serial number: {$serialNumber}";
                        continue;
                    }

                    // Verify item is dispatched (quantity should be 0 or item should have dispatch record)
                    $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                        ->whereNull('streetlight_pole_id')
                        ->where('isDispatched', true)
                        ->first();

                    if (!$dispatch) {
                        $errors[] = "Item with serial number {$serialNumber} is not dispatched or is already consumed.";
                        continue;
                    }

                    // Return the item
                    $inventory->quantity = 1;
                    $inventory->save();

                    // Log history
                    $project = Project::find($inventory->project_id);
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logReturned(
                        $inventory,
                        $inventoryType,
                        $inventory->project_id,
                        $inventory->store_id,
                        1
                    );

                    // Delete dispatch record
                    $dispatch->delete();
                    $returnedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to return inventory item', [
                        'serial_number' => $serialNumber,
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Failed to return item {$serialNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            if ($returnedCount > 0) {
                $message = "{$returnedCount} item(s) returned successfully.";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " item(s) failed: " . implode(', ', array_slice($errors, 0, 3));
                }
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'returned_count' => $returnedCount,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No items were returned. ' . implode(', ', array_slice($errors, 0, 3))
                ], 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Bulk return failed', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while returning inventory items: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Download inventory import format template
     */
    public function downloadImportFormat($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $projectType = $project->project_type;

            $filename = 'inventory_import_format_' . ($projectType == 1 ? 'streetlight' : 'rooftop') . '_' . date('Y-m-d') . '.xlsx';

            return Excel::download(
                new InventoryImportFormatExport($projectType),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Error downloading inventory import format: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download import format template.');
        }
    }

}
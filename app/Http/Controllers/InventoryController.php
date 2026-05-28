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
use App\Models\Pole;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use App\Services\Inventory\InventoryHistoryService;
use App\Services\Logging\ActivityLogger;
use App\Support\StreetlightInventoryItems;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Inventory Lifecycle Management — handles the full lifecycle of inventory items: receiving
 * stock via Excel GRN imports (both rooftop and streetlight types), adding individual items with
 * serial number & SIM uniqueness validation, dispatching items to field vendors (decrementing
 * store quantity), tracking dispatch history, and supporting item return/replacement flows. Item
 * codes are customer-defined; item behavior is inferred from item name/category.
 *
 * Data Flow:
 *   GRN Excel Upload → Validation (serial/SIM uniqueness) → InventoryService → DB Insert
 *   → Dispatch: Select serials → Validate stock → Create InventoryDispatch records →
 *   Decrement quantity → Log history → Return/Replace: Reverse dispatch flow
 *
 * @depends-on InventoryServiceInterface, InventoryHistoryService, ActivityLogger, InventoryDispatch, InventroyStreetLightModel, Inventory, Project, Stores
 * @business-domain Inventory & Warehouse
 * @package App\Http\Controllers
 */
class InventoryController extends Controller
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService,
        protected InventoryHistoryService $historyService,
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Import data from file.
     *
     * Data flow: HTTP Request → Validation → Database → Redirect with status
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        $projectId = $request->projectId;
        $storeId = $request->storeId;
        try {
            Excel::import(new InventoryImport($projectId, $storeId), $request->file('file'));

            $this->activityLogger->log('inventory', 'imported', null, [
                'description' => 'Rooftop inventory imported from Excel.',
                'project_id' => $projectId,
                'extra' => [
                    'store_id' => $storeId,
                ],
            ]);

            if ($storeId) {
                return redirect()
                    ->to(route('store.show', $storeId).'#view')
                    ->with('success', 'Inventory imported successfully!');
            }

            return redirect()->route('projects.index')->with('success', 'Inventory imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Import streetlight data from file.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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

            if (! empty($errors)) {
                // Build a downloadable errors.txt file
                $lines = [];
                $lines[] = 'Inventory Import Errors - '.now()->toDateTimeString();
                $lines[] = 'Project ID: '.$projectId.', Store ID: '.$storeId;
                $lines[] = str_repeat('=', 80);

                foreach ($errors as $err) {
                    $reason = $err['reason'] ?? 'unknown';
                    $itemCode = $err['item_code'] ?? '';
                    $serial = $err['serial_number'] ?? '';
                    $item = $err['item'] ?? '';
                    $sim = $err['sim_number'] ?? '';
                    $message = $err['message'] ?? null;

                    $lines[] = "Reason: {$reason}";
                    if ($message) {
                        $lines[] = "  Message: {$message}";
                    }
                    $lines[] = "  Item Code: {$itemCode}";
                    $lines[] = "  Item: {$item}";
                    $lines[] = "  Serial: {$serial}";
                    if ($sim) {
                        $lines[] = "  SIM: {$sim}";
                    }
                    $lines[] = str_repeat('-', 40);
                }

                $content = implode(PHP_EOL, $lines).PHP_EOL;

                // Use the public disk so URL generation is reliable
                $disk = Storage::disk('public');
                if (! $disk->exists('import_errors')) {
                    $disk->makeDirectory('import_errors');
                }

                $fileName = 'inventory_errors_project_'.$projectId.'_store_'.$storeId.'_'.time().'.txt';
                $relativePath = 'import_errors/'.$fileName;
                $disk->put($relativePath, $content);

                // Public URL (requires `php artisan storage:link` once)
                $errorFileUrl = $disk->url($relativePath);
            }

            // On success, stay on store page and open View Inventory tab
            $this->activityLogger->log('inventory', 'imported', null, [
                'description' => 'Streetlight inventory imported from Excel.',
                'project_id' => $projectId,
                'extra' => [
                    'store_id' => $storeId,
                    'imported' => $importedCount,
                    'skipped' => count($errors),
                ],
            ]);

            $redirect = redirect()->to(route('store.show', $storeId).'#view')
                ->with('import_errors_url', $errorFileUrl)
                ->with('import_errors_count', count($errors))
                ->with('import_first_error', $errors[0]['message'] ?? null);

            if ($importedCount > 0) {
                $message = "Inventory imported successfully! Imported rows: {$importedCount}";
                if (! empty($errors)) {
                    $message .= ', Skipped rows: '.count($errors);
                }
                $redirect->with('success', $message);
            } else {
                // No new rows imported - treat as warning/error
                $message = 'No new inventory imported. Skipped rows: '.count($errors);
                $redirect->withErrors(['error' => $message]);
            }

            return $redirect;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $projectType = (int) $request->project_type;
            $itemCode = StreetlightInventoryItems::normalizeCode($request->input('code') ?? $request->input('item_code'));
            $serialNumber = $request->input('serialnumber') ?? $request->input('serial_number');

            $itemName = $request->input('dropdown') ?? $request->input('item');

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

            // Validate sim_number uniqueness for luminary items only.
            if ($projectType == 1 && StreetlightInventoryItems::isLuminary($itemName, $itemCode) && $request->filled('sim_number')) {
                $existingSim = InventroyStreetLightModel::where('sim_number', $request->sim_number)
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
                    ->to(route('store.show', $storeId).'#view')
                    ->with('success', 'Inventory added successfully.');
            }

            $this->activityLogger->log('inventory', 'created', null, [
                'description' => "Added new inventory item {$itemCode} ({$serialNumber})",
                'extra' => ['project_type' => $projectType]
            ]);

            return redirect()->route('projects.index')
                ->with('success', 'Inventory added successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating inventory: '.$e->getMessage());

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

            if (! $serialNumber) {
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
            Log::error('Error checking serial number: '.$e->getMessage());

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Try to find in both inventory models
            $item = InventroyStreetLightModel::find($id);
            if (! $item) {
                $item = Inventory::findOrFail($id);
            }
            $itemDesc = $item->productName ?? $item->item ?? "ID: {$id}";
            $item->delete();

            $this->activityLogger->log('inventory', 'deleted', null, [
                'description' => "Deleted inventory {$itemDesc} (#{$id})"
            ]);

            return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete Item'], 500);
        }
    }

    /**
     * View inventory.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function viewInventory(Request $request)
    {
        try {
            $projectId = $request->project_id;
            $storeId = $request->store_id;

            if (! $projectId || ! $storeId) {
                return redirect()->route('projects.index')
                    ->with('error', 'Please select a project and store to view inventory.');
            }

            $store = Stores::findOrFail($storeId);
            $storeName = $store->store_name;
            $storeIncharge = $store->store_incharge_id ? User::find($store->store_incharge_id) : null;
            $inchargeName = $storeIncharge ? trim($storeIncharge->firstName.' '.$storeIncharge->lastName) : 'N/A';

            $project = Project::findOrFail($projectId);
            $projectType = $project->project_type;
            $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

            $inventoryBaseQuery = $inventoryModel::query()
                ->where('project_id', $projectId)
                ->where('store_id', $storeId);

            // Keep the existing unified view behavior, but fetch only required columns.
            $allInventory = (clone $inventoryBaseQuery)
                ->select([
                    'id',
                    'item_code',
                    'item',
                    'serial_number',
                    'manufacturer',
                    'make',
                    'model',
                    'hsn',
                    'quantity',
                    'rate',
                    'created_at',
                ])
                ->where('store_id', $storeId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Load dispatches once and reuse for row status + summary metrics.
            $allDispatched = InventoryDispatch::query()
                ->where('isDispatched', true)
                ->where('store_id', $storeId)
                ->where('project_id', $projectId)
                ->with('vendor:id,firstName,lastName')
                ->select([
                    'id',
                    'item_code',
                    'serial_number',
                    'dispatch_date',
                    'vendor_id',
                    'is_consumed',
                    'total_value',
                ])
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
                    $vendorName = $dispatch->vendor ? ($dispatch->vendor->firstName.' '.$dispatch->vendor->lastName) : null;

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

            // For backward compatibility with the existing blade view.
            $inventory = collect($unifiedInventory);

            $dispatchSummary = InventoryDispatch::query()
                ->where('isDispatched', true)
                ->where('store_id', $storeId)
                ->where('project_id', $projectId)
                ->select('item_code')
                ->selectRaw('COUNT(*) as dispatched_quantity')
                ->selectRaw('SUM(COALESCE(total_value, 0)) as dispatched_value')
                ->groupBy('item_code')
                ->get()
                ->keyBy('item_code');

            $summaryByCategory = [
                StreetlightInventoryItems::CATEGORY_BATTERY => ['quantity' => 0, 'value' => 0.0, 'dispatch' => 0, 'dispatch_value' => 0.0],
                StreetlightInventoryItems::CATEGORY_LUMINARY => ['quantity' => 0, 'value' => 0.0, 'dispatch' => 0, 'dispatch_value' => 0.0],
                StreetlightInventoryItems::CATEGORY_PANEL => ['quantity' => 0, 'value' => 0.0, 'dispatch' => 0, 'dispatch_value' => 0.0],
                StreetlightInventoryItems::CATEGORY_STRUCTURE => ['quantity' => 0, 'value' => 0.0, 'dispatch' => 0, 'dispatch_value' => 0.0],
            ];

            foreach ($allInventory->groupBy('item_code') as $code => $itemsForCode) {
                $firstItem = $itemsForCode->first();
                $category = StreetlightInventoryItems::category($firstItem->item ?? null, $code);
                if (! isset($summaryByCategory[$category])) {
                    continue;
                }

                $dispatch = $dispatchSummary->get($code);
                $summaryByCategory[$category]['quantity'] += $itemsForCode->count();
                $summaryByCategory[$category]['value'] += $itemsForCode->sum(fn($item) => (float) ($item->quantity ?? 0) * (float) ($item->rate ?? 0));
                $summaryByCategory[$category]['dispatch'] += (int) optional($dispatch)->dispatched_quantity;
                $summaryByCategory[$category]['dispatch_value'] += (float) optional($dispatch)->dispatched_value;
            }

            $batterySummary = $summaryByCategory[StreetlightInventoryItems::CATEGORY_BATTERY];
            $totalBattery = $batterySummary['quantity'];
            $totalBatteryValue = number_format($batterySummary['value'], 2);
            $batteryDispatch = $batterySummary['dispatch'];
            $availableBattery = max($totalBattery - $batteryDispatch, 0);
            $dispatchAmountBattery = number_format($batterySummary['dispatch_value'], 2);

            $luminarySummary = $summaryByCategory[StreetlightInventoryItems::CATEGORY_LUMINARY];
            $totalLuminary = $luminarySummary['quantity'];
            $totalLuminaryValue = number_format($luminarySummary['value'], 2);
            $luminaryDispatch = $luminarySummary['dispatch'];
            $availableLuminary = max($totalLuminary - $luminaryDispatch, 0);
            $dispatchAmountLuminary = number_format($luminarySummary['dispatch_value'], 2);

            $structureSummary = $summaryByCategory[StreetlightInventoryItems::CATEGORY_STRUCTURE];
            $totalStructure = $structureSummary['quantity'] ?: $totalBattery;
            $totalStructureValue = $structureSummary['value'] > 0 ? number_format($structureSummary['value'], 2) : $totalBattery * 400;
            $structureDispatch = $structureSummary['dispatch'] ?: $batteryDispatch;
            $availableStructure = max($totalStructure - $structureDispatch, 0);

            $moduleSummary = $summaryByCategory[StreetlightInventoryItems::CATEGORY_PANEL];
            $totalModule = $moduleSummary['quantity'];
            $totalModuleValue = number_format($moduleSummary['value'], 2);
            $moduleDispatch = $moduleSummary['dispatch'];
            $availableModule = max($totalModule - $moduleDispatch, 0);
            $dispatchAmountModule = number_format($moduleSummary['dispatch_value'], 2);

            // Get distinct item codes for filter
            $itemCodes = (clone $inventoryBaseQuery)
                ->select('item_code')
                ->distinct()
                ->orderBy('item_code')
                ->pluck('item_code')
                ->values();

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
            Log::error('Unable to render inventory view.', [
                'project_id' => $request->project_id,
                'store_id' => $request->store_id,
                'exception' => $e,
            ]);

            return redirect()->route('projects.index')
                ->with('error', 'Unable to load inventory for the selected store.');
        }
    }

    /**
     * Dispatch Inventory to a vendor
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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
                'serial_numbers.*' => 'required|string',
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
                $errorMessage = "Not enough stock for {$request->item}. Available: {$availableQuantity}";
                $this->logDispatchError($request, $errorMessage, 'insufficient_stock');
                
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage,
                    ], 422);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }

            foreach ($request->serial_numbers as $serialNumber) {
                // Find the specific inventory item with this serial number
                $inventoryItem = $inventoryModel::where('serial_number', $serialNumber)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->where('quantity', '>', 0)
                    ->first();

                if (! $inventoryItem) {
                    $errorMessage = "Item with serial number {$serialNumber} not found or already dispatched";
                    $this->logDispatchError($request, $errorMessage, 'serial_not_found');
                    
                    if ($request->ajax()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $errorMessage,
                        ], 422);
                    }
                    
                    return redirect()->back()->with('error', $errorMessage);
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
                    'total_quantity' => '1',
                    'total_value' => $request->rate,
                    'serial_number' => $serialNumber,
                    'dispatch_date' => Carbon::now(),
                    'isDispatched' => true,
                ]);
                $inventoryItem->decrement('quantity', 1);

                // Log history
                $inventoryType = ($project->project_type == 1) ? 'streetlight' : 'rooftop';
                $this->historyService->logDispatched($dispatch, $inventoryItem, $inventoryType);

                $dispatchedItems[] = $dispatch;
            }

            if (count($dispatchedItems) > 0) {
                $this->activityLogger->log('inventory', 'dispatched', $project, [
                    'description' => "Dispatched " . count($dispatchedItems) . " items to vendor {$request->vendor_id} via Web UI"
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventory dispatched successfully',
                ]);
            }

            return redirect()->back()->with('success', 'Inventory dispatched successfully');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first();
            $this->logDispatchError($request, $errorMessage, 'validation');
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage,
                    'errors' => $e->errors(),
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->logDispatchError($request, $errorMessage, 'exception');
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage,
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Inventory dispatched Failed');
        }
    }

    /**
     * Log dispatch error in human-readable format for client support
     */
    private function logDispatchError(Request $request, string $errorMessage, string $errorType = 'error'): void
    {
        $storeId = $request->store_id;
        $projectId = $request->project_id;
        $vendorId = $request->vendor_id;
        $itemCode = $request->item_code ?? 'N/A';
        $serialNumbers = $request->serial_numbers ?? [];
        $serialNumbersStr = is_array($serialNumbers) ? implode(', ', $serialNumbers) : (string) $serialNumbers;
        
        // Load store and project names if IDs are available
        $storeName = 'N/A';
        if ($storeId) {
            $store = Stores::find($storeId);
            $storeName = $store ? $store->store_name : "ID: {$storeId}";
        }
        
        $projectName = 'N/A';
        if ($projectId) {
            $project = Project::find($projectId);
            $projectName = $project ? $project->project_name : "ID: {$projectId}";
        }
        
        $logMessage = sprintf(
            'Issue Material Failed | Store: %s (ID: %s) | Project: %s (ID: %s) | Vendor ID: %s | Item: %s | Serials: %s | Error: %s',
            $storeName,
            $storeId ?? 'N/A',
            $projectName,
            $projectId ?? 'N/A',
            $vendorId ?? 'N/A',
            $itemCode,
            $serialNumbersStr ?: 'N/A',
            $errorMessage
        );
        
        Log::error($logMessage);
    }

    /**
     * Bulk dispatch inventory from Excel file
     */
    public function bulkDispatchFromExcel(Request $request)
    {
        try {
            // Increase execution time for large file processing
            set_time_limit(600); // 10 minutes
            ini_set('max_execution_time', '600');

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
            $allSerialNumbers = [];
            $allItemCodes = [];

            // First pass: Detect duplicates within the uploaded file and collect serial numbers
            foreach ($data as $row) {
                $serialNumber = $row['serial_number'] ?? $row['SERIAL_NUMBER'] ?? null;
                $itemCode = StreetlightInventoryItems::normalizeCode($row['item_code'] ?? $row['ITEM_CODE'] ?? null);
                if ($serialNumber) {
                    if (isset($seenSerials[$serialNumber])) {
                        $seenSerials[$serialNumber]++;
                    } else {
                        $seenSerials[$serialNumber] = 1;
                        $allSerialNumbers[] = $serialNumber;
                        if ($itemCode) {
                            $allItemCodes[$serialNumber] = $itemCode;
                        }
                    }
                }
            }

            // Optimize: Load all inventory items at once to reduce database queries
            $inventoryItems = collect();
            if (! empty($allSerialNumbers)) {
                $inventoryItems = $inventoryModel::whereIn('serial_number', $allSerialNumbers)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->serial_number.'_'.$item->item_code;
                    });
            }

            // Optimize: Load all existing dispatches at once
            $existingDispatches = InventoryDispatch::whereIn('serial_number', $allSerialNumbers)
                ->where('isDispatched', true)
                ->pluck('serial_number')
                ->toArray();
            $existingDispatchesSet = array_flip($existingDispatches);

            // Second pass: Categorize items
            foreach ($data as $row) {
                $itemCode = StreetlightInventoryItems::normalizeCode($row['item_code'] ?? $row['ITEM_CODE'] ?? null);
                $itemName = $row['item'] ?? $row['ITEM NAME'] ?? $row['item_name'] ?? null;
                $serialNumber = $row['serial_number'] ?? $row['SERIAL_NUMBER'] ?? null;
                $isLuminary = StreetlightInventoryItems::isLuminary($itemName, $itemCode);
                $simNumber = $isLuminary ? ($row['sim_number'] ?? $row['SIM_NUMBER'] ?? null) : null;

                if (! $itemCode || ! $itemName || ! $serialNumber) {
                    $nonExisting[] = [
                        'item_code' => $itemCode ?? 'N/A',
                        'item' => $itemName ?? 'N/A',
                        'serial_number' => $serialNumber ?? 'N/A',
                        'sim_number' => $simNumber,
                        'reason' => 'Missing required fields: item_code, item, or serial_number',
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

                // Check if serial number exists in inventory (from pre-loaded collection)
                $inventoryItem = $inventoryItems->get($serialNumber.'_'.$itemCode);

                if (! $inventoryItem) {
                    $nonExisting[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                        'reason' => "Serial number {$serialNumber} not found in inventory",
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
                        'reason' => "Serial number {$serialNumber} has quantity {$inventoryItem->quantity}, expected 1",
                    ];

                    continue;
                }

                // Check if already dispatched (using pre-loaded data)
                if (isset($existingDispatchesSet[$serialNumber])) {
                    $alreadyDispatched[] = [
                        'item_code' => $itemCode,
                        'item' => $itemName,
                        'serial_number' => $serialNumber,
                        'sim_number' => $simNumber,
                    ];

                    continue;
                }

                // Validate SIM number for luminary items
                if ($isLuminary && $simNumber) {
                    $existingSim = InventroyStreetLightModel::where('sim_number', $simNumber)
                        ->where('id', '!=', $inventoryItem->id)
                        ->exists();

                    if ($existingSim) {
                        $nonExisting[] = [
                            'item_code' => $itemCode,
                            'item' => $itemName,
                            'serial_number' => $serialNumber,
                            'sim_number' => $simNumber,
                            'reason' => "SIM number {$simNumber} already exists for another luminary item",
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
            $this->logDispatchError($request, 'Failed to process bulk dispatch: '.$e->getMessage(), 'bulk_dispatch_exception');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process bulk dispatch: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm and dispatch items from bulk upload preview
     */
    public function confirmBulkDispatch(Request $request)
    {
        try {
            // Increase execution time for bulk dispatch processing
            set_time_limit(600); // 10 minutes
            ini_set('max_execution_time', '600');

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
                // Optimize: Load all inventory items at once to reduce database queries
                $inventoryItems = $inventoryModel::whereIn('serial_number', $serialNumbers)
                    ->where('project_id', $request->project_id)
                    ->where('store_id', $request->store_id)
                    ->where('quantity', '>', 0)
                    ->get()
                    ->keyBy('serial_number');

                // Check for already dispatched items in bulk
                $existingDispatches = InventoryDispatch::whereIn('serial_number', $serialNumbers)
                    ->where('isDispatched', true)
                    ->pluck('serial_number')
                    ->toArray();
                $existingDispatchesSet = array_flip($existingDispatches);

                foreach ($serialNumbers as $serialNumber) {
                    // Find the inventory item from pre-loaded collection
                    $inventoryItem = $inventoryItems->get($serialNumber);

                    if (! $inventoryItem) {
                        $failedItems[] = [
                            'serial_number' => $serialNumber,
                            'error' => 'Item not found or out of stock',
                        ];

                        continue;
                    }

                    // Double-check if already dispatched (race condition protection) - using pre-loaded data
                    if (isset($existingDispatchesSet[$serialNumber])) {
                        $failedItems[] = [
                            'serial_number' => $serialNumber,
                            'error' => 'Item already dispatched',
                        ];

                        continue;
                    }

                    // Double-check with a fresh query for race condition protection
                    $existingDispatch = InventoryDispatch::where('serial_number', $serialNumber)
                        ->where('isDispatched', true)
                        ->exists();

                    if ($existingDispatch) {
                        $failedItems[] = [
                            'serial_number' => $serialNumber,
                            'error' => 'Item already dispatched',
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

                $message = 'Successfully dispatched '.count($dispatchedItems).' item(s)';
                if (count($dispatchedItems) > 0) {
                    $this->activityLogger->log('inventory', 'dispatched', $project, [
                        'description' => "Bulk dispatched " . count($dispatchedItems) . " items to vendor {$request->vendor_id}"
                    ]);
                }

                if (count($failedItems) > 0) {
                    $message .= '. '.count($failedItems).' item(s) failed to dispatch.';
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
            $this->logDispatchError($request, 'Failed to confirm bulk dispatch: '.$e->getMessage(), 'bulk_confirm_exception');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm bulk dispatch: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * View vendor inventory.
     *
     * @param  mixed  $vendorId  The vendor identifier
     * @return void  
     */
    public function viewVendorInventory($vendorId)
    {
        Log::info('viewVendorInventory', ['vendorId' => $vendorId]);
        try {
            // ── 1. Existence check + project meta (single lightweight query) ──────────
            $meta = InventoryDispatch::where('vendor_id', $vendorId)
                ->with('project:id,project_name')
                ->select('project_id')
                ->first();

            if (!$meta) {
                Log::warning("No inventory found for vendor_id: {$vendorId}");
                return response()->json([
                    'message'   => 'No inventory found for this vendor.',
                    'vendor_id' => $vendorId,
                ], 404);
            }

            // ── 2. Aggregate totals per item_code + is_consumed bucket via SQL ────────
            //    This replaces loading all rows into PHP memory.
            $aggregates = InventoryDispatch::where('vendor_id', $vendorId)
                ->selectRaw('
                    item_code,
                    item,
                    model,
                    rate,
                    store_id,
                    store_incharge_id,
                    is_consumed,
                    COUNT(*) AS total_quantity,
                    COUNT(*) * rate AS total_value
                ')
                ->groupBy('item_code', 'item','model', 'rate',
                          'store_id', 'store_incharge_id', 'is_consumed')
                ->with(['store:id,store_name', 'storeIncharge:id,firstName,lastName'])
                ->get()
                ->groupBy('item_code');   // small result set — safe in PHP

            // ── 3. Build the three buckets using chunked dispatch queries ─────────────
            $totalReceived = [];
            $inStock       = [];
            $consumed      = [];

            foreach ($aggregates as $itemCode => $rows) {
                // Rows for this item_code: one row per (is_consumed) value
                $allRow      = null;
                $inStockRow  = null;
                $consumedRow = null;

                foreach ($rows as $row) {
                    if ((int)$row->is_consumed === 0) {
                        $inStockRow = $row;
                    } else {
                        $consumedRow = $row;
                    }
                    // Use any row for the "all" meta (item details are the same)
                    $allRow = $row;
                }

                // Fetch dispatches for this item_code in chunks to avoid memory spikes
                $allDispatches      = $this->fetchDispatchesChunked($vendorId, $itemCode, null);
                $inStockDispatches  = $this->fetchDispatchesChunked($vendorId, $itemCode, 0);
                $consumedDispatches = $this->fetchDispatchesChunked($vendorId, $itemCode, 1);

                $totalReceived[] = $this->buildItemPayload(
                    $allRow,
                    (int)($allRow->total_quantity ?? 0) + (int)(optional($consumedRow)->total_quantity ?? 0),
                    $allDispatches
                );

                if ($inStockRow) {
                    $inStock[] = $this->buildItemPayload($inStockRow, (int)$inStockRow->total_quantity, $inStockDispatches);
                }

                if ($consumedRow) {
                    $consumed[] = $this->buildItemPayload($consumedRow, (int)$consumedRow->total_quantity, $consumedDispatches);
                }
            }

            // Re-compute total_received quantity/value as sum of in_stock + consumed
            foreach ($totalReceived as &$item) {
                $code = $item['item_code'];
                $inStockQty   = collect($inStock)->firstWhere('item_code', $code)['total_quantity'] ?? 0;
                $consumedQty  = collect($consumed)->firstWhere('item_code', $code)['total_quantity'] ?? 0;
                $item['total_quantity'] = $inStockQty + $consumedQty;
                $item['total_value']    = $item['total_quantity'] * $item['rate'];
            }
            unset($item);

            $sumQuantity = fn($arr) => array_sum(array_column($arr, 'total_quantity'));
            $sumValue    = fn($arr) => array_sum(array_column($arr, 'total_value'));

            return response()->json([
                'vendor_id'    => $vendorId,
                'project_id'   => optional($meta->project)->id,
                'project_name' => optional($meta->project)->project_name,
                'all_inventory' => [
                    'total_received' => [
                        'total' => ['quantity' => $sumQuantity($totalReceived), 'value' => $sumValue($totalReceived)],
                        'items' => $totalReceived,
                    ],
                    'in_stock' => [
                        'total' => ['quantity' => $sumQuantity($inStock), 'value' => $sumValue($inStock)],
                        'items' => $inStock,
                    ],
                    'consumed' => [
                        'total' => ['quantity' => $sumQuantity($consumed), 'value' => $sumValue($consumed)],
                        'items' => $consumed,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stream dispatch rows for a given vendor/item_code/is_consumed bucket in chunks,
     * building the dispatch array without ever holding all rows in memory at once.
     *
     * @param  int|string  $vendorId
     * @param  string      $itemCode
     * @param  int|null    $isConsumed  null = all, 0 = in-stock, 1 = consumed
     * @return array
     */
    private function fetchDispatchesChunked($vendorId, string $itemCode, ?int $isConsumed): array
    {
        $dispatches = [];

        $query = InventoryDispatch::where('vendor_id', $vendorId)
            ->where('item_code', $itemCode)
            ->select('dispatch_date', 'serial_number', 'total_quantity', 'is_consumed');

        if (!is_null($isConsumed)) {
            $query->where('is_consumed', $isConsumed);
        }

        $query->orderBy('dispatch_date')
              ->chunk(500, function ($rows) use (&$dispatches) {
                      $allSerials = [];
                      $parsedRows = [];

                      foreach ($rows as $row) {
                          $serials = collect(json_decode($row->serial_number, true) ?? [$row->serial_number])
                              ->filter()->values()->all();
                          $parsedRows[] = ['row' => $row, 'serials' => $serials];
                          array_push($allSerials, ...$serials);
                      }

                      // One DB round-trip for the entire chunk
                      $simMap = \App\Models\InventroyStreetLightModel::whereIn('serial_number', array_unique($allSerials))
                          ->pluck('sim_number', 'serial_number');

                      foreach ($parsedRows as ['row' => $row, 'serials' => $serials]) {
                          $dispatches[] = [
                              'dispatch_date' => $row->dispatch_date,
                              'quantity'      => $row->total_quantity,
                              'items'         => array_map(fn($sn) => [
                                  'serial_number' => $sn,
                                  'sim_number'    => $simMap[$sn] ?? null,
                                  'dispatch_date' => $row->dispatch_date,
                              ], $serials),
                          ];
                      }
              });

        return $dispatches;
    }

    /**
     * Build the item-level payload from an aggregate row + pre-built dispatch list.
     */
    private function buildItemPayload($row, int $totalQuantity, array $dispatches): array
    {
        return [
            'item_code'      => $row->item_code,
            'item'           => $row->item,
            'manufacturer'   => $row->manufacturer ?? null,
            'make'           => $row->make ?? null,
            'model'          => $row->model,
            'rate'           => (float) $row->rate,
            'total_quantity' => $totalQuantity,
            'total_value'    => $totalQuantity * (float) $row->rate,
            'store_name'     => optional($row->store)->store_name,
            'store_incharge' => trim(optional($row->storeIncharge)->firstName . ' ' .
                                     optional($row->storeIncharge)->lastName),
            'dispatches'     => $dispatches,
        ];
    }

    /**
     * Check q r.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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

    /**
     * Show dispatch inventory.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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

    /**
     * Return inventory.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function returnInventory(Request $request)
    {
        $serial_number = $request->input('serial_number');
        try {
            $inventory = InventroyStreetLightModel::where('serial_number', $serial_number)->first();
            $dispatch = InventoryDispatch::where('serial_number', $serial_number)->whereNull('streetlight_pole_id')->first();
            if (! $inventory) {
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

    /**
     * Replace item.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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

            if (! $newDispatch) {
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
                    'streetlight_pole_id',
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

            $this->activityLogger->log('inventory', 'replaced', null, [
                'description' => "Replaced item {$request->old_serial_number} with new item {$request->new_serial_number}"
            ]);

            DB::commit();

            if (isset($oldStreet)) {
                $oldStreet->quantity = 1;
                $oldStreet->save();
            }

            if ($pole) {
                $newItemName = isset($newStreet) ? $newStreet->item : ($newDispatch->item ?? null);
                $qrColumn = StreetlightInventoryItems::poleQrColumn($newItemName, $newDispatch->item_code);
                if ($qrColumn) {
                    $pole->{$qrColumn} = $newSerial;
                }
                $pole->save();
            }
            DB::commit();

            return back()->with('success', 'Item replaced successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('replace_error', 'Failed to replace item: '.$e->getMessage());
        }
    }

    /**
     * Perform bulk delete operation.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            // Handle both array and JSON string formats
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            if (! is_array($ids) || empty($ids)) {
                return response()->json([
                    'message' => 'No valid items selected for deletion.',
                ], 400);
            }

            // Try to delete from both inventory models
            $deletedCount = 0;
            $deletedCount += InventroyStreetLightModel::whereIn('id', $ids)->delete();
            $deletedCount += Inventory::whereIn('id', $ids)->delete();

            if ($deletedCount > 0) {
                return response()->json([
                    'message' => "{$deletedCount} item(s) deleted successfully.",
                ]);
            } else {
                return response()->json([
                    'message' => 'No items were deleted. Please check if the selected items exist.',
                ], 400);
            }
        } catch (\Throwable $th) {
            Log::error('Bulk delete error: '.$th->getMessage());

            return response()->json([
                'message' => 'An error occurred while deleting inventory items: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Perform bulk return operation.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function bulkReturn(Request $request)
    {
        try {
            $serialNumbers = $request->input('serial_numbers', []);

            if (! is_array($serialNumbers) || empty($serialNumbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid items selected for return.',
                ], 400);
            }

            $returnedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($serialNumbers as $serialNumber) {
                try {
                    $inventory = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();

                    if (! $inventory) {
                        $errors[] = "Inventory item not found for serial number: {$serialNumber}";

                        continue;
                    }

                    // Verify item is dispatched (quantity should be 0 or item should have dispatch record)
                    $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                        ->whereNull('streetlight_pole_id')
                        ->where('isDispatched', true)
                        ->first();

                    if (! $dispatch) {
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
                        'error' => $e->getMessage(),
                    ]);
                    $errors[] = "Failed to return item {$serialNumber}: ".$e->getMessage();
                }
            }

            DB::commit();

            if ($returnedCount > 0) {
                $message = "{$returnedCount} item(s) returned successfully.";
                if (! empty($errors)) {
                    $message .= ' '.count($errors).' item(s) failed: '.implode(', ', array_slice($errors, 0, 3));
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'returned_count' => $returnedCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No items were returned. '.implode(', ', array_slice($errors, 0, 3)),
                ], 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Bulk return failed', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while returning inventory items: '.$th->getMessage(),
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

            $filename = 'inventory_import_format_'.($projectType == 1 ? 'streetlight' : 'rooftop').'_'.date('Y-m-d').'.xlsx';

            return Excel::download(
                new InventoryImportFormatExport($projectType),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Error downloading inventory import format: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to download import format template.');
        }
    }

    /**
     * Swap two inventory items.
     *
     * Pole swap  (both consumed)  : swap streetlight_pole_id on both dispatches + swap the QR
     *                               field on both Pole records. Admin-only.
     * Vendor swap (both dispatched, not consumed): swap vendor_id on both dispatches. Admin-only.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function swapInventory(Request $request)
    {
        $request->validate([
            'serial_number_1' => 'required|string',
            'serial_number_2' => 'required|string|different:serial_number_1',
        ]);

        // Admin only
        if (auth()->user()->role !== \App\Enums\UserRole::ADMIN->value) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        $serial1 = trim($request->serial_number_1);
        $serial2 = trim($request->serial_number_2);

        $dispatch1 = InventoryDispatch::where('serial_number', $serial1)->first();
        $dispatch2 = InventoryDispatch::where('serial_number', $serial2)->first();

        if (! $dispatch1) {
            return response()->json(['success' => false, 'message' => "Item not found: {$serial1}"], 422);
        }
        if (! $dispatch2) {
            return response()->json(['success' => false, 'message' => "Item not found: {$serial2}"], 422);
        }
        if ($dispatch1->item_code !== $dispatch2->item_code) {
            return response()->json([
                'success' => false,
                'message' => 'Items must be of the same type (same item code) to swap.',
            ], 422);
        }

        $both_consumed   = $dispatch1->is_consumed  && $dispatch2->is_consumed;
        $both_dispatched = $dispatch1->isDispatched  && $dispatch2->isDispatched
                           && ! $dispatch1->is_consumed && ! $dispatch2->is_consumed;

        if (! $both_consumed && ! $both_dispatched) {
            return response()->json([
                'success' => false,
                'message' => 'Both items must be in the same state: either both consumed (pole swap) or both dispatched but not consumed (vendor swap).',
            ], 422);
        }

        try {
            DB::transaction(function () use ($dispatch1, $dispatch2, $both_consumed, $serial1, $serial2) {

                if ($both_consumed) {
                    // ── Pole swap ──
                    $poleId1 = $dispatch1->streetlight_pole_id;
                    $poleId2 = $dispatch2->streetlight_pole_id;

                    if (! $poleId1 || ! $poleId2) {
                        throw new \RuntimeException('One or both consumed items are not linked to a pole.');
                    }

                    $pole1 = Pole::findOrFail($poleId1);
                    $pole2 = Pole::findOrFail($poleId2);

                    // Determine which QR field holds each serial number
                    $qrFields = ['luminary_qr', 'panel_qr', 'battery_qr'];
                    $field1 = collect($qrFields)->first(fn($f) => $pole1->$f === $serial1);
                    $field2 = collect($qrFields)->first(fn($f) => $pole2->$f === $serial2);

                    if (! $field1 || ! $field2) {
                        throw new \RuntimeException('Could not match serial numbers to QR fields on the poles.');
                    }

                    // Swap pole IDs on dispatches
                    $dispatch1->streetlight_pole_id = $poleId2;
                    $dispatch1->save();
                    $dispatch2->streetlight_pole_id = $poleId1;
                    $dispatch2->save();

                    // Swap QR values on poles (field type must be same since item_code matches)
                    $pole1->$field1 = $serial2;
                    $pole1->save();
                    $pole2->$field2 = $serial1;
                    $pole2->save();

                } else {
                    // ── Vendor swap ──
                    $vendorId1 = $dispatch1->vendor_id;
                    $vendorId2 = $dispatch2->vendor_id;

                    $dispatch1->vendor_id = $vendorId2;
                    $dispatch1->save();
                    $dispatch2->vendor_id = $vendorId1;
                    $dispatch2->save();
                }

                // Log both swaps to inventory history
                $swapType  = $both_consumed ? 'pole_swap' : 'vendor_swap';
                $this->historyService->log('swapped', [
                    'serial_number'    => $serial1,
                    'project_id'       => $dispatch1->project_id,
                    'store_id'         => $dispatch1->store_id,
                    'metadata'         => ['swap_type' => $swapType, 'swapped_with' => $serial2],
                ]);
                $this->historyService->log('swapped', [
                    'serial_number'    => $serial2,
                    'project_id'       => $dispatch2->project_id,
                    'store_id'         => $dispatch2->store_id,
                    'metadata'         => ['swap_type' => $swapType, 'swapped_with' => $serial1],
                ]);

                $this->activityLogger->log('inventory', 'swapped', null, [
                    'description' => "Swapped {$serial1} ↔ {$serial2} ({$swapType})",
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Items swapped successfully.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Inventory swap failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Swap failed: '.$e->getMessage(),
            ], 500);
        }
    }
}

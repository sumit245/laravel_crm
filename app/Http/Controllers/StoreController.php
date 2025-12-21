<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use App\Policies\StorePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $storeModel = Stores::all();
        //   return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $projectId = null)
    {
        try {
            // Get projectId from request if not provided as parameter (for sidebar context)
            $projectId = $projectId ?? $request->input('project_id');

            if (!$projectId) {
                return redirect()->back()->with('error', 'Project ID is required.');
            }

            // Check authorization
            $policy = new StorePolicy();
            if (!$policy->create(auth()->user(), $projectId)) {
                abort(403, 'Unauthorized. Only Admin can create stores.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'storeIncharge' => 'required|exists:users,id',
            ]);

            Stores::create([
                'project_id' => $projectId,
                'store_name' => $validated['name'],
                'address' => $validated['address'],
                'store_incharge_id' => $validated['storeIncharge'],
            ]);

            $project = Project::with('stores')->findOrFail($projectId);
            $users = User::where('role', '!=', \App\Enums\UserRole::VENDOR->value)->get();
            // TODO: Also remove role 1
            // Redirect back to the project detail page with updated data
            return redirect()->back()->with('success', 'Store Created Successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create store. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Stores::with(['project', 'storeIncharge'])->findOrFail($id);
        $project = $store->project;

        if (!$project) {
            abort(404, 'Project not found for this store.');
        }

        $user = auth()->user();
        $isAdmin = $user->role === \App\Enums\UserRole::ADMIN->value;

        // Check authorization for Project Managers
        if ($user->role === \App\Enums\UserRole::PROJECT_MANAGER->value) {
            $isAssigned = \Illuminate\Support\Facades\DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You do not have access to this store.');
            }
        }

        // Get inventory data - Optimized: Only select needed columns and limit results
        $inventoryModel = ($project->project_type == 1) ? \App\Models\InventroyStreetLightModel::class : \App\Models\Inventory::class;

        // SERVER-SIDE PAGINATION: Don't load all inventory items here
        // The view will use server-side pagination via AJAX endpoint
        // This allows handling millions of rows efficiently
        $unifiedInventory = []; // Empty array - data loaded via AJAX
        
        // Get distinct item codes for filter (query only for filter dropdown)
        $inventoryModel = ($project->project_type == 1) ? \App\Models\InventroyStreetLightModel::class : \App\Models\Inventory::class;
        $itemCodes = $inventoryModel::where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->distinct()
            ->pluck('item_code')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // For backward compatibility - empty collections since we're using server-side pagination
        $inStock = collect([]);
        $dispatched = collect([]);

        // Optimized: Only get aggregated dispatched data, not all records
        $dispatchedAggregated = \App\Models\InventoryDispatch::where('store_id', $store->id)
            ->where('isDispatched', true)
            ->whereNotNull('item_code')
            ->select('item_code', \Illuminate\Support\Facades\DB::raw('SUM(total_quantity) as total_quantity'), \Illuminate\Support\Facades\DB::raw('SUM(total_value) as total_value'))
            ->groupBy('item_code')
            ->get()
            ->keyBy('item_code');

        // Calculate metrics
        $initialStockValue = 0;
        $inStoreStockValue = 0;
        $dispatchedStockValue = 0;

        if ($project->project_type == 1) {
            try {
                $initialStockValue = $inventoryModel::where('project_id', $project->id)
                    ->where('store_id', $store->id)
                    ->sum(\Illuminate\Support\Facades\DB::raw('quantity * rate')) ?? 0;

                // Use aggregated data instead of collection sum
                $dispatchedStockValue = $dispatchedAggregated->sum(function ($item) {
                    $value = $item->total_value ?? 0;
                    return is_numeric($value) ? (float) $value : 0;
                });

                $inStoreStockValue = max(0, (float) $initialStockValue - (float) $dispatchedStockValue);
            } catch (\Exception $e) {
                Log::error('Error calculating stock values for store ' . $store->id . ': ' . $e->getMessage());
                $initialStockValue = 0;
                $dispatchedStockValue = 0;
                $inStoreStockValue = 0;
            }
        }

        // Get item-wise statistics - Fixed to match SQL query logic
        $itemStats = [];
        if ($project->project_type == 1) {
            try {
                $items = ['SL01' => 'Panel', 'SL02' => 'Luminary', 'SL03' => 'Battery', 'SL04' => 'Structure'];

                // Single query to get all item statistics grouped by item_code
                // Total Received: COUNT(*) - total number of records
                // Current Stock: SUM(CASE WHEN quantity = 1 THEN 1 ELSE 0 END) - count where quantity = 1
                // Total Dispatched: SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) - count where quantity = 0
                $inventoryStats = $inventoryModel::where('project_id', $project->id)
                    ->where('store_id', $store->id)
                    ->whereIn('item_code', array_keys($items))
                    ->select(
                        'item_code',
                        \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_received'),
                        \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN quantity = 1 THEN 1 ELSE 0 END) as current_stock'),
                        \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as total_dispatched')
                    )
                    ->groupBy('item_code')
                    ->get()
                    ->keyBy('item_code');

                foreach ($items as $code => $name) {
                    try {
                        $totalReceived = isset($inventoryStats[$code]) && is_numeric($inventoryStats[$code]->total_received)
                            ? (int) $inventoryStats[$code]->total_received
                            : 0;

                        $inStockCount = isset($inventoryStats[$code]) && is_numeric($inventoryStats[$code]->current_stock)
                            ? (int) $inventoryStats[$code]->current_stock
                            : 0;

                        $dispatchedCount = isset($inventoryStats[$code]) && is_numeric($inventoryStats[$code]->total_dispatched)
                            ? (int) $inventoryStats[$code]->total_dispatched
                            : 0;

                        $itemStats[$code] = [
                            'name' => $name,
                            'total' => $totalReceived,
                            'dispatched' => $dispatchedCount,
                            'in_stock' => $inStockCount,
                        ];
                    } catch (\Exception $e) {
                        Log::error("Error calculating stats for item {$code} in store {$store->id}: " . $e->getMessage());
                        // Set default values for this item
                        $itemStats[$code] = [
                            'name' => $name,
                            'total' => 0,
                            'dispatched' => 0,
                            'in_stock' => 0,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error calculating item stats for store ' . $store->id . ': ' . $e->getMessage());
                // Ensure itemStats is at least an empty array
                $itemStats = [];
            }
        }

        // Get users for store incharge selection - Optimized: Only select needed columns
        $users = \App\Models\User::where('role', '!=', \App\Enums\UserRole::VENDOR->value)
            ->select('id', 'firstName', 'lastName', 'role')
            ->orderBy('firstName')
            ->get();

        // Get assigned vendors for dispatch modal
        $assignedVendorIds = \Illuminate\Support\Facades\DB::table('project_user')
            ->where('project_user.project_id', $project->id)
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('users.role', \App\Enums\UserRole::VENDOR->value)
            ->pluck('project_user.user_id')
            ->toArray();

        $assignedVendors = \App\Models\User::whereIn('id', $assignedVendorIds)->get();

        // Get inventory items for dispatch modal (grouped by item_code for streetlight projects)
        $inventoryItems = collect([]);
        if ($project->project_type == 1) {
            $inventoryItems = $inventoryModel::where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->select(
                    'item_code',
                    'item',
                    \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_quantity'),
                    \Illuminate\Support\Facades\DB::raw('MAX(rate) as rate'),
                    \Illuminate\Support\Facades\DB::raw('SUM(quantity * rate) as total_value'),
                    \Illuminate\Support\Facades\DB::raw('MAX(make) as make'),
                    \Illuminate\Support\Facades\DB::raw('MAX(model) as model')
                )
                ->groupBy('item_code', 'item')
                ->get();
        } else {
            $inventoryItems = $inventoryModel::where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->get();
        }

        return view('stores.show', compact('store', 'project', 'inStock', 'dispatched', 'unifiedInventory', 'itemCodes', 'initialStockValue', 'inStoreStockValue', 'dispatchedStockValue', 'itemStats', 'users', 'inventoryModel', 'assignedVendors', 'inventoryItems', 'isAdmin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Server-side pagination endpoint for inventory DataTable
     */
    public function inventoryData(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Check authorization
        $user = auth()->user();
        if ($user->role === \App\Enums\UserRole::PROJECT_MANAGER->value) {
            $isAssigned = \Illuminate\Support\Facades\DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isAssigned) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';
        
        // Build base query - optimized structure
        $query = \Illuminate\Support\Facades\DB::table($inventoryTable . ' as inv')
            ->leftJoin('inventory_dispatch as disp', function($join) {
                $join->on('inv.serial_number', '=', 'disp.serial_number')
                     ->where('disp.isDispatched', '=', true);
            })
            ->leftJoin('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->where('inv.project_id', $project->id)
            ->where('inv.store_id', $store->id)
            ->select(
                'inv.id',
                'inv.item_code',
                'inv.item',
                'inv.serial_number',
                \Illuminate\Support\Facades\DB::raw('COALESCE(inv.quantity, 0) as quantity'),
                'inv.created_at',
                'disp.id as dispatch_id',
                'disp.is_consumed',
                'disp.dispatch_date',
                \Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name')
            );

        // OPTIMIZATION: Fast count using base table (no JOINs) for recordsTotal
        // This is much faster and accurate since LEFT JOINs don't change the row count
        // Each inventory item = 1 row, regardless of JOINs
        $startTime = microtime(true);
        $totalRecords = \Illuminate\Support\Facades\DB::table($inventoryTable)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->count();
        $countTime = microtime(true) - $startTime;
        
        // Store total count - this will be used for recordsTotal
        // recordsFiltered will be calculated after applying filters
        $baseTotalRecords = $totalRecords;

        // Apply search filter
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function($q) use ($search) {
                $q->where('inv.item_code', 'like', "%{$search}%")
                  ->orWhere('inv.item', 'like', "%{$search}%")
                  ->orWhere('inv.serial_number', 'like', "%{$search}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        // Apply column filters
        if ($request->filled('availability')) {
            $availability = $request->input('availability');
            if ($availability === 'In Stock') {
                $query->where('inv.quantity', '>', 0);
            } elseif ($availability === 'Dispatched') {
                $query->whereNotNull('disp.id')->where('disp.is_consumed', '!=', 1);
            } elseif ($availability === 'Consumed') {
                $query->where('disp.is_consumed', '=', 1);
            }
        }

        if ($request->filled('item_code')) {
            $query->where('inv.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $query->where(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        // Count filtered records - use fast count if no filters applied
        $hasFilters = $request->filled('search.value') || 
                     $request->filled('availability') || 
                     $request->filled('item_code') || 
                     $request->filled('vendor_name');
        
        if (!$hasFilters) {
            // No filters = same as total (fast path)
            $filteredRecords = $baseTotalRecords;
        } else {
            // Has filters - need to count with JOINs (slower but accurate)
            $filterStartTime = microtime(true);
            $filteredRecords = (clone $query)->count();
            $filterCountTime = microtime(true) - $filterStartTime;
        }
        
        // recordsFiltered should match the filtered count
        // Note: filteredRecords can be different from baseTotalRecords when filters are applied
        // This is correct behavior - don't cap it

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 6); // Default to created_at
        $orderDirection = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'item_code', 'item', 'serial_number', 'availability', 'vendor_name', 'dispatch_date', 'created_at'];
        
        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'vendor_name') {
                $query->orderBy(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), $orderDirection);
            } elseif ($column === 'availability') {
                $query->orderBy(\Illuminate\Support\Facades\DB::raw('CASE 
                    WHEN COALESCE(inv.quantity, 0) > 0 THEN "In Stock"
                    WHEN disp.is_consumed = 1 THEN "Consumed"
                    WHEN disp.id IS NOT NULL THEN "Dispatched"
                    ELSE "In Stock"
                END'), $orderDirection);
            } else {
                $query->orderBy('inv.' . $column, $orderDirection);
            }
        } else {
            $query->orderBy('inv.created_at', 'desc');
        }

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 50);
        
        // OPTIMIZATION: Return only requested length (50) - no preloading
        // The query is slow, so we'll optimize it with indexes instead of preloading data
        $actualLength = $length;
        
        // Time the data query - CRITICAL: This measures actual query performance
        $dataStartTime = microtime(true);

        // If length is -1, get all records (for export)
        if ($length == -1) {
            $items = $query->get();
        } else {
            $items = $query->skip($start)->take($actualLength)->get();
        }
        
        $dataQueryTime = microtime(true) - $dataStartTime;

        // Format data for DataTables
        $data = $items->map(function($item) use ($user) {
            $availability = 'In Stock';
            if ($item->quantity > 0) {
                $availability = 'In Stock';
            } elseif ($item->is_consumed == 1) {
                $availability = 'Consumed';
            } elseif ($item->dispatch_id) {
                $availability = 'Dispatched';
            }

            $vendorName = trim($item->vendor_name ?? '') ?: '-';
            $dispatchDate = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $createdAt = $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-';

            $row = [];
            if ($user->role === \App\Enums\UserRole::ADMIN->value) {
                $row[] = '<input type="checkbox" class="row-checkbox" value="' . $item->id . '" data-id="' . $item->id . '" data-availability="' . $availability . '" data-item-code="' . $item->item_code . '" data-vendor-name="' . htmlspecialchars($vendorName) . '">';
            }
            $row[] = $item->item_code;
            $row[] = $item->item;
            $row[] = $item->serial_number;
            $row[] = '<span class="badge bg-' . ($availability === 'In Stock' ? 'success' : ($availability === 'Dispatched' ? 'warning' : 'danger')) . '">' . $availability . '</span>';
            $row[] = $vendorName;
            $row[] = $dispatchDate;
            $row[] = $createdAt;
            
            // Actions column
            $actions = '';
            if ($availability === 'In Stock' && $user->role === \App\Enums\UserRole::ADMIN->value) {
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-item" data-id="' . $item->id . '" title="Delete"><i class="mdi mdi-delete"></i></button>';
            } elseif ($availability === 'Dispatched') {
                $actions .= '<form action="' . route('inventory.return') . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to return this item?\');">';
                $actions .= csrf_field();
                $actions .= '<input type="hidden" name="serial_number" value="' . $item->serial_number . '">';
                $actions .= '<button type="submit" class="btn btn-sm btn-warning" title="Return"><i class="mdi mdi-undo"></i></button>';
                $actions .= '</form>';
            } elseif ($availability === 'Consumed') {
                $actions .= '<button type="button" class="btn btn-sm btn-primary replace-item" data-dispatch-id="' . ($item->dispatch_id ?? '') . '" data-serial-number="' . $item->serial_number . '" title="Replace"><i class="mdi mdi-swap-horizontal"></i></button>';
            }
            $row[] = $actions;

            return $row;
        });

        $response = [
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $baseTotalRecords, // Total records (with JOINs, no filters)
            'recordsFiltered' => $filteredRecords, // Filtered records (with JOINs and filters)
            'data' => $data,
        ];

        return response()->json($response);
    }

    /**
     * Export inventory to Excel with all filtered data
     */
    public function exportInventory(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Determine inventory table based on project type
        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        // Build the same query as inventoryData but get ALL filtered records
        $query = \Illuminate\Support\Facades\DB::table($inventoryTable . ' as inv')
            ->leftJoin('inventory_dispatch as disp', function($join) {
                $join->on('inv.serial_number', '=', 'disp.serial_number')
                     ->where('disp.isDispatched', '=', true);
            })
            ->leftJoin('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->where('inv.project_id', $project->id)
            ->where('inv.store_id', $store->id)
            ->select(
                'inv.id',
                'inv.item_code',
                'inv.item',
                'inv.serial_number',
                \Illuminate\Support\Facades\DB::raw('COALESCE(inv.quantity, 0) as quantity'),
                'inv.created_at',
                'disp.id as dispatch_id',
                'disp.is_consumed',
                'disp.dispatch_date',
                \Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name')
            );

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('inv.item_code', 'like', "%{$search}%")
                  ->orWhere('inv.item', 'like', "%{$search}%")
                  ->orWhere('inv.serial_number', 'like', "%{$search}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        // Apply column filters
        if ($request->filled('availability')) {
            $availability = $request->input('availability');
            if ($availability === 'In Stock') {
                $query->where('inv.quantity', '>', 0);
            } elseif ($availability === 'Dispatched') {
                $query->whereNotNull('disp.id')->where('disp.is_consumed', '!=', 1);
            } elseif ($availability === 'Consumed') {
                $query->where('disp.is_consumed', '=', 1);
            }
        }

        if ($request->filled('item_code')) {
            $query->where('inv.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $query->where(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        // Get all filtered records (no pagination)
        $items = $query->orderBy('inv.created_at', 'desc')->get();

        // Format data for Excel export (remove HTML tags)
        $exportData = $items->map(function($item) {
            $availability = 'In Stock';
            if ($item->quantity > 0) {
                $availability = 'In Stock';
            } elseif ($item->is_consumed == 1) {
                $availability = 'Consumed';
            } elseif ($item->dispatch_id) {
                $availability = 'Dispatched';
            }

            $vendorName = trim($item->vendor_name ?? '') ?: '-';
            $dispatchDate = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $createdAt = $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-';

            return [
                'Item Code' => $item->item_code,
                'Item' => $item->item,
                'Serial Number' => $item->serial_number,
                'Availability' => $availability,
                'Vendor' => $vendorName,
                'Dispatch Date' => $dispatchDate,
                'In Date' => $createdAt,
            ];
        })->toArray();

        // Generate filename based on filters
        $date = now()->format('dmY'); // ddmmyyyy format
        $hasFilters = $request->filled('availability') || $request->filled('item_code') || $request->filled('vendor_name');
        
        if ($hasFilters) {
            // Case 2: With filters - Inv_MUZ_DIS_VEN_20122025.xlsx
            $storeName = strtoupper(substr($store->name ?? 'STORE', 0, 3));
            $parts = ['Inv', $storeName];
            
            // Add availability filter (3 letters)
            if ($request->filled('availability')) {
                $avail = $request->input('availability');
                if ($avail === 'In Stock') {
                    $parts[] = 'INS';
                } elseif ($avail === 'Dispatched') {
                    $parts[] = 'DIS';
                } elseif ($avail === 'Consumed') {
                    $parts[] = 'CON';
                }
            }
            
            // Add vendor filter (3 letters)
            if ($request->filled('vendor_name')) {
                $vendor = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $request->input('vendor_name')), 0, 3));
                $parts[] = $vendor ?: 'VEN';
            }
            
            // Add item code filter (3 letters)
            if ($request->filled('item_code')) {
                $item = strtoupper(substr($request->input('item_code'), 0, 3));
                $parts[] = $item;
            }
            
            $parts[] = $date;
            $filename = implode('_', $parts) . '.xlsx';
        } else {
            // Case 1: No filters - Inventory_Muzaffarpur_20122025.xlsx (full store name)
            $storeName = $store->name ?? 'Store';
            $filename = 'Inventory_' . $storeName . '_' . $date . '.xlsx';
        }

        // Use ExcelHelper to export
        return \App\Helpers\ExcelHelper::exportToExcel($exportData, $filename);
    }

    /**
     * Server-side pagination endpoint for dispatched items DataTable
     */
    public function dispatchedData(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Check authorization
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Build base query for dispatched items
        $query = \Illuminate\Support\Facades\DB::table('inventory_dispatch as disp')
            ->join('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->leftJoin('inventory_streetlight as inv', 'disp.serial_number', '=', 'inv.serial_number')
            ->where('disp.store_id', $store->id)
            ->where('disp.project_id', $project->id)
            ->where('disp.isDispatched', true)
            ->select(
                'disp.id',
                'disp.item_code',
                'disp.item',
                'disp.serial_number',
                'disp.dispatch_date',
                'disp.total_value',
                \Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name')
            );

        // Count total records
        $startTime = microtime(true);
        $totalRecords = \Illuminate\Support\Facades\DB::table('inventory_dispatch')
            ->where('store_id', $store->id)
            ->where('project_id', $project->id)
            ->where('isDispatched', true)
            ->count();
        $countTime = microtime(true) - $startTime;
        
        $baseTotalRecords = $totalRecords;

        // Apply search filter
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function($q) use ($search) {
                $q->where('disp.item_code', 'like', "%{$search}%")
                  ->orWhere('disp.item', 'like', "%{$search}%")
                  ->orWhere('disp.serial_number', 'like', "%{$search}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        // Apply column filters
        if ($request->filled('item_code')) {
            $query->where('disp.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $query->where(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        if ($request->filled('dispatch_date')) {
            $query->whereDate('disp.dispatch_date', $request->input('dispatch_date'));
        }

        // Count filtered records
        $hasFilters = $request->filled('search.value') || 
                     $request->filled('item_code') || 
                     $request->filled('vendor_name') ||
                     $request->filled('dispatch_date');
        
        if (!$hasFilters) {
            $filteredRecords = $baseTotalRecords;
        } else {
            $filterStartTime = microtime(true);
            $filteredRecords = (clone $query)->count();
            $filterCountTime = microtime(true) - $filterStartTime;
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 4); // Default to dispatch_date
        $orderDirection = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'item_code', 'item', 'serial_number', 'vendor_name', 'dispatch_date', 'total_value'];
        
        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'vendor_name') {
                $query->orderBy(\Illuminate\Support\Facades\DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), $orderDirection);
            } else {
                $query->orderBy('disp.' . $column, $orderDirection);
            }
        } else {
            $query->orderBy('disp.dispatch_date', 'desc');
        }

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 50);
        $actualLength = $length;

        // Get data
        $dataStartTime = microtime(true);
        if ($length == -1) {
            $items = $query->get();
        } else {
            $items = $query->skip($start)->take($actualLength)->get();
        }
        $dataQueryTime = microtime(true) - $dataStartTime;

        // Format data for DataTables
        $data = $items->map(function($item) {
            $vendorName = trim($item->vendor_name ?? '') ?: '-';
            $dispatchDate = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $totalValue = number_format($item->total_value ?? 0, 2);

            return [
                '<input type="checkbox" class="row-checkbox" value="' . $item->id . '" data-id="' . $item->id . '">',
                $item->item_code ?? 'N/A',
                $item->item ?? 'N/A',
                $item->serial_number ?? 'N/A',
                $vendorName,
                $dispatchDate,
                '₹' . $totalValue,
                '<button type="button" class="btn btn-sm btn-danger delete-item" data-id="' . $item->id . '" data-url="' . route('inventory.destroy', $item->id) . '"><i class="mdi mdi-delete"></i></button>',
            ];
        });

        $response = [
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $baseTotalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $store = Stores::findOrFail($id);
            $store->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

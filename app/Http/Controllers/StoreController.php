<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use App\Policies\StorePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Enums\UserRole;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storeModel = Stores::all();
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
        $isAdmin = $user->role === UserRole::ADMIN->value;

        // Check authorization
        if ($user->role === UserRole::PROJECT_MANAGER->value) {
            $isAssigned = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You do not have access to this store.');
            }
        }

        $inventoryTableName = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';
        $inventoryModel = ($project->project_type == 1) ? \App\Models\InventroyStreetLightModel::class : \App\Models\Inventory::class;

        // OPTIMIZATION 1: Get Total Count separately (Fast, No Joins)
        // This is passed to 'deferLoading' so DataTables knows there are 1M+ rows
        // without us actually loading them all.
        // Note: Exclude SL04 from count as it's not shown in table
        $inventoryTotal = DB::table($inventoryTableName)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->where('item_code', '!=', 'SL04') // Exclude SL04 from table count
            ->count();
        Log::info('Inventory Total: ' . $inventoryTotal);

        // Subquery: one dispatch per (serial_number, store, project) - prefer consumed, else most recent
        $dispSubInit = DB::table('inventory_dispatch')
            ->select(
                'serial_number',
                'store_id',
                'project_id',
                DB::raw('CAST(SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY (streetlight_pole_id IS NOT NULL) DESC, id DESC), ",", 1) AS UNSIGNED) as best_disp_id')
            )
            ->where('isDispatched', true)
            ->where('store_id', $store->id)
            ->where('project_id', $project->id)
            ->groupBy('serial_number', 'store_id', 'project_id');

        // OPTIMIZATION 2: Initial Data Load (Fast) - Only load exactly paginated rows
        // Note: SL04 (Structure) items are excluded from the table as they are mapped to SL03 (Battery)
        $unifiedInventory = DB::table($inventoryTableName . ' as inv')
            ->leftJoinSub($dispSubInit, 'disp_best', function ($join) {
                $join->on('inv.serial_number', '=', 'disp_best.serial_number')
                    ->on('inv.store_id', '=', 'disp_best.store_id')
                    ->on('inv.project_id', '=', 'disp_best.project_id');
            })
            ->leftJoin('inventory_dispatch as disp', 'disp.id', '=', 'disp_best.best_disp_id')
            ->leftJoin('streelight_poles as pole', 'disp.streetlight_pole_id', '=', 'pole.id')
            ->leftJoin('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->where('inv.project_id', $project->id)
            ->where('inv.store_id', $store->id)
            ->where('inv.item_code', '!=', 'SL04') // Exclude SL04 from table display
            ->select(array_merge(
                [
                    'inv.id',
                    'inv.item_code',
                    'inv.item',
                    'inv.serial_number',
                    DB::raw('COALESCE(inv.quantity, 0) as quantity'),
                    'inv.created_at',
                    'inv.received_date',
                    'disp.id as dispatch_id',
                    'disp.is_consumed',
                    'disp.streetlight_pole_id',
                    'disp.dispatch_date',
                    DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name'),
                ],
                $inventoryTableName === 'inventory_streetlight'
                    ? [DB::raw('CASE WHEN inv.item_code = "SL02" THEN COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) ELSE NULL END as sim_number')]
                    : [DB::raw('NULL as sim_number')]
            ))
            ->orderBy('inv.created_at', 'desc')
            // ->limit(2000)
            ->get();

        // OPTIMIZATION 3: Metrics Calculation using Aggregates
        // Use SUM/COUNT in DB instead of loading Models to PHP memory
        $initialStockValue = 0;
        $inStoreStockValue = 0;
        $dispatchedStockValue = 0;
        $inStoreStockQuantity = 0;
        $dispatchedStockQuantity = 0;
        $itemStats = [];

        if ($project->project_type == 1) {
            $initialStockValue = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->sum(DB::raw('quantity * rate'));

            $dispatchedStockValue = DB::table('inventory_dispatch')
                ->where('store_id', $store->id)
                ->where('isDispatched', true)
                ->sum('total_value');

            $inStoreStockValue = max(0, (float) $initialStockValue - (float) $dispatchedStockValue);

            // Item Stats (Single optimized query)
            // Note: SL04 (Structure) stats are mapped to SL03 (Battery) - they should match
            $items = ['SL01' => 'Panel', 'SL02' => 'Luminary', 'SL03' => 'Battery', 'SL04' => 'Structure'];

            // Get stats for SL01, SL02, SL03 (exclude SL04 from query as it's mapped to SL03)
            $statsData = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->whereIn('item_code', ['SL01', 'SL02', 'SL03'])
                ->select(
                    'item_code',
                    DB::raw('COUNT(*) as total_received'),
                    // Assuming quantity 1 is in-stock, 0 is dispatched
                    DB::raw('SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as current_stock')
                )
                ->groupBy('item_code')
                ->get()
                ->keyBy('item_code');

            // Calculate stats for SL01, SL02, SL03
            foreach (['SL01', 'SL02', 'SL03'] as $code) {
                $stat = $statsData->get($code);
                $total = $stat ? $stat->total_received : 0;
                $stock = $stat ? $stat->current_stock : 0;

                $itemStats[$code] = [
                    'name' => $items[$code],
                    'total' => $total,
                    'in_stock' => $stock,
                    'dispatched' => $total - $stock
                ];
            }

            // Map SL04 (Structure) stats to match SL03 (Battery) stats
            $sl03Stats = $itemStats['SL03'] ?? ['total' => 0, 'in_stock' => 0, 'dispatched' => 0];
            $itemStats['SL04'] = [
                'name' => $items['SL04'],
                'total' => $sl03Stats['total'],
                'in_stock' => $sl03Stats['in_stock'],
                'dispatched' => $sl03Stats['dispatched']
            ];

            // Calculate total quantities for pie chart (sum across all items including SL04)
            $inStoreStockQuantity = array_sum(array_column($itemStats, 'in_stock'));
            $dispatchedStockQuantity = array_sum(array_column($itemStats, 'dispatched'));
        }

        // Dropdowns (Lightweight)
        $users = User::where('role', '!=', UserRole::VENDOR->value)
            ->select('id', 'firstName', 'lastName', 'role')
            ->orderBy('firstName')
            ->get();

        $assignedVendorIds = DB::table('project_user')
            ->where('project_user.project_id', $project->id)
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('users.role', UserRole::VENDOR->value)
            ->pluck('project_user.user_id')
            ->toArray();

        $assignedVendors = User::whereIn('id', $assignedVendorIds)->get();

        // Distinct Item Codes for Filters (exclude SL04 as it's not in table)
        $itemCodes = DB::table($inventoryTableName)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->where('item_code', '!=', 'SL04') // Exclude SL04 from filter options
            ->distinct()
            ->pluck('item_code');

        // Inventory Items for Dispatch Form (exclude SL04 as it's not manually dispatched)
        $inventoryItems = collect([]);
        if ($project->project_type == 1) {
            $inventoryItems = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->where('item_code', '!=', 'SL04') // Exclude SL04 from dispatch form (imported from GRN only)
                ->select(
                    'item_code',
                    'item',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('MAX(rate) as rate'),
                    DB::raw('MAX(make) as make'),
                    DB::raw('MAX(model) as model')
                )
                ->groupBy('item_code', 'item')
                ->get();
            Log::info('Inventory Items: ' . $inventoryItems->count());
        } else {
            $inventoryItems = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->get();
        }

        // Pass empty collections for backward compatibility if needed, though view uses $unifiedInventory
        $inStock = collect([]);
        $dispatched = collect([]);

        return view('stores.show', compact(
            'store', 'project', 'unifiedInventory', 'inventoryTotal',
            'initialStockValue', 'inStoreStockValue', 'dispatchedStockValue',
            'inStoreStockQuantity', 'dispatchedStockQuantity',
            'itemStats', 'users', 'assignedVendors', 'inventoryItems',
            'isAdmin', 'itemCodes', 'inStock', 'dispatched', 'inventoryModel'
        ));
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
        Log::info("Ajax request received for inventory data");
        Log::info("Store ID: " . $storeId);
        
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;

        $user = auth()->user();
        $isAdmin = $user->role === UserRole::ADMIN->value;

        if ($user->role === UserRole::PROJECT_MANAGER->value) {
            $hasAccess = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAccess) return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        // Subquery: one dispatch per (serial_number, store, project) - prefer consumed, else most recent
        $dispSub = DB::table('inventory_dispatch')
            ->select(
                'serial_number',
                'store_id',
                'project_id',
                DB::raw('CAST(SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY (streetlight_pole_id IS NOT NULL) DESC, id DESC), ",", 1) AS UNSIGNED) as best_disp_id')
            )
            ->where('isDispatched', true)
            ->where('store_id', $store->id)
            ->where('project_id', $project->id)
            ->groupBy('serial_number', 'store_id', 'project_id');

        // 1. Base Query Structure - join to single dispatch per inv to prevent duplicate rows on sort
        // Note: SL04 (Structure) items are excluded from the table as they are mapped to SL03 (Battery)
        $query = DB::table($inventoryTable . ' as inv')
            ->leftJoinSub($dispSub, 'disp_best', function ($join) {
                $join->on('inv.serial_number', '=', 'disp_best.serial_number')
                    ->on('inv.store_id', '=', 'disp_best.store_id')
                    ->on('inv.project_id', '=', 'disp_best.project_id');
            })
            ->leftJoin('inventory_dispatch as disp', 'disp.id', '=', 'disp_best.best_disp_id')
            ->leftJoin('streelight_poles as pole', 'disp.streetlight_pole_id', '=', 'pole.id')
            ->leftJoin('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->where('inv.project_id', $project->id)
            ->where('inv.store_id', $store->id)
            ->where('inv.item_code', '!=', 'SL04') // Exclude SL04 from table display
            ->select(array_merge(
                [
                    'inv.id',
                    'inv.item_code',
                    'inv.item',
                    'inv.serial_number',
                    DB::raw('COALESCE(inv.quantity, 0) as quantity'),
                    'inv.created_at',
                    'inv.received_date',
                    'disp.id as dispatch_id',
                    'disp.is_consumed',
                    'disp.streetlight_pole_id',
                    'disp.dispatch_date',
                    DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name'),
                ],
                $inventoryTable === 'inventory_streetlight'
                    ? [DB::raw('CASE WHEN inv.item_code = "SL02" THEN COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) ELSE NULL END as sim_number')]
                    : [DB::raw('NULL as sim_number')]
            ));

        // 2. Count Total (Fastest way - No Joins for total count)
        // Note: Exclude SL04 from count as it's not shown in table
        $recordsTotal = DB::table($inventoryTable)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->where('item_code', '!=', 'SL04') // Exclude SL04 from table count
            ->count();

        // 3. Filtering
        $isFiltered = false;

        // Global Search
        if ($request->filled('search.value')) {
            $isFiltered = true;
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('inv.item_code', 'like', "%{$search}%")
                    ->orWhere('inv.item', 'like', "%{$search}%")
                    ->orWhere('inv.serial_number', 'like', "%{$search}%")
                    ->orWhere(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        // Custom Filters
        if ($request->filled('availability')) {
            $isFiltered = true;
            $avail = $request->input('availability');
            if ($avail === 'In Stock') {
                $query->where('inv.quantity', '>', 0)->whereNull('disp.id');
            } elseif ($avail === 'Dispatched') {
                $query->whereNotNull('disp.id')
                    ->whereRaw('COALESCE(inv.quantity, 0) <= 0')
                    ->whereNull('disp.streetlight_pole_id');
            } elseif ($avail === 'Consumed') {
                $query->whereNotNull('disp.streetlight_pole_id');
            }
        }

        if ($request->filled('item_code')) {
            $isFiltered = true;
            $query->where('inv.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $isFiltered = true;
            $query->where(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        // 4. Count Filtered (Only run expensive count if filtered)
        $recordsFiltered = $isFiltered ? $query->count() : $recordsTotal;

        // 5. Ordering
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:sim, 5:avail, 6:vendor, 7:disp_date, 8:in_date, 9:act]
        // User:  [0:code, 1:item, 2:serial, 3:sim, 4:avail, 5:vendor, 6:disp_date, 7:in_date, 8:act]
        $orderColumnIndex = $request->input('order.0.column', $isAdmin ? 8 : 7); // Default 'created_at' index
        $orderDir = $request->input('order.0.dir', 'desc');

        // Map dataTable column index to DB columns
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:sim, 5:avail, 6:vendor, 7:disp_date, 8:in_date, 9:act]
        // User:  [0:code, 1:item, 2:serial, 3:sim, 4:avail, 5:vendor, 6:disp_date, 7:in_date, 8:act]

        $columnsAdmin = [
            1 => 'item_code', 2 => 'item', 3 => 'serial_number', 4 => 'sim_number',
            5 => 'availability', 6 => 'vendor_name', 7 => 'dispatch_date', 8 => 'created_at'
        ];
        $columnsUser = [
            0 => 'item_code', 1 => 'item', 2 => 'serial_number', 3 => 'sim_number',
            4 => 'availability', 5 => 'vendor_name', 6 => 'dispatch_date', 7 => 'created_at'
        ];

        $colMap = $isAdmin ? $columnsAdmin : $columnsUser;
        // TODO: by default sort by created_at latest entries on the top
        $sortCol = $colMap[$orderColumnIndex] ?? 'created_at';

        if ($sortCol === 'vendor_name') {
            $query->orderBy(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), $orderDir);
        } elseif ($sortCol === 'availability') {
             $query->orderBy(DB::raw('CASE
                WHEN disp.streetlight_pole_id IS NOT NULL THEN "Consumed"
                WHEN disp.id IS NOT NULL THEN "Dispatched"
                WHEN COALESCE(inv.quantity, 0) > 0 THEN "In Stock"
                ELSE "In Stock"
            END'), $orderDir);
        } elseif ($sortCol === 'dispatch_date') {
            $query->orderBy('disp.dispatch_date', $orderDir);
        } elseif ($sortCol === 'sim_number' && $inventoryTable === 'inventory_streetlight') {
            $query->orderBy('inv.sim_number', $orderDir);
        } else {
            $query->orderBy('inv.' . $sortCol, $orderDir);
        }

        // 6. Pagination (Critical for performance)
        $start = $request->input('start', 0);
        $length = $request->input('length', 50);

        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $items = $query->get();

        // 7. Formatting
        $data = $items->map(function ($item) use ($isAdmin) {
            // Availability: dispatch status takes precedence over quantity
            // Consumed = installed on pole | Dispatched = with vendor | In Stock = neither
            $availability = 'In Stock';
            if (!empty($item->streetlight_pole_id)) {
                $availability = 'Consumed';
            } elseif ($item->dispatch_id) {
                $availability = 'Dispatched';
            } elseif (($item->quantity ?? 0) > 0) {
                $availability = 'In Stock';
            }

            $vendorName = trim($item->vendor_name ?? '') ?: '-';
            $dispatchDate = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $receivedDate = $item->received_date
                ? \Carbon\Carbon::parse($item->received_date)->format('d/m/Y')
                : ($item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-');

            $serialCell = $item->serial_number;
            if ($availability === 'Consumed' && !empty($item->streetlight_pole_id)) {
                $serialCell = '<a href="' . route('poles.show', $item->streetlight_pole_id) . '" class="text-primary" style="text-decoration:none">' . htmlspecialchars($item->serial_number) . '</a>';
            }

            // SIM number only for SL02 (Luminary); SL01/SL03/SL04 always show "-"
            $simCell = (($item->item_code ?? '') === 'SL02' && trim((string)($item->sim_number ?? '')) !== '')
                ? htmlspecialchars($item->sim_number)
                : '-';

            $row = [];

            if ($isAdmin) {
                $row[] = '<input type="checkbox" class="row-checkbox" value="' . $item->id . '" data-id="' . $item->id . '" data-serial-number="' . htmlspecialchars($item->serial_number) . '" data-availability="' . $availability . '" data-item-code="' . $item->item_code . '" data-vendor-name="' . htmlspecialchars($vendorName) . '">';
            }

            $row[] = $item->item_code;
            $row[] = $item->item;
            $row[] = $serialCell;
            $row[] = $simCell;
            $row[] = '<span class="badge bg-' . ($availability === 'In Stock' ? 'success' : ($availability === 'Dispatched' ? 'warning' : 'danger')) . '">' . $availability . '</span>';
            $row[] = $vendorName;
            $row[] = $dispatchDate;
            $row[] = $receivedDate;

            $actions = '';
            if ($availability === 'In Stock' && $isAdmin) {
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-item" data-id="' . $item->id . '" title="Delete"><i class="mdi mdi-delete"></i></button>';
            } elseif ($availability === 'Dispatched') {
                $actions .= '<form action="' . route('inventory.return') . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to return this item?\');">'. csrf_field() . '<input type="hidden" name="serial_number" value="' . $item->serial_number . '"><button type="submit" class="btn btn-sm btn-warning" title="Return"><i class="mdi mdi-undo"></i></button></form>';
            } elseif ($availability === 'Consumed') {
                $actions .= '<button type="button" class="btn btn-sm btn-primary replace-item" data-dispatch-id="' . ($item->dispatch_id ?? '') . '" data-serial-number="' . $item->serial_number . '" title="Replace"><i class="mdi mdi-swap-horizontal"></i></button>';
            }
            $row[] = $actions;

            return $row;
        });

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
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

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        $dispSubExport = DB::table('inventory_dispatch')
            ->select(
                'serial_number',
                'store_id',
                'project_id',
                DB::raw('CAST(SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY (streetlight_pole_id IS NOT NULL) DESC, id DESC), ",", 1) AS UNSIGNED) as best_disp_id')
            )
            ->where('isDispatched', true)
            ->where('store_id', $store->id)
            ->where('project_id', $project->id)
            ->groupBy('serial_number', 'store_id', 'project_id');

        // Note: SL04 (Structure) items are excluded from export as they are mapped to SL03 (Battery)
        $query = DB::table($inventoryTable . ' as inv')
            ->leftJoinSub($dispSubExport, 'disp_best', function ($join) {
                $join->on('inv.serial_number', '=', 'disp_best.serial_number')
                    ->on('inv.store_id', '=', 'disp_best.store_id')
                    ->on('inv.project_id', '=', 'disp_best.project_id');
            })
            ->leftJoin('inventory_dispatch as disp', 'disp.id', '=', 'disp_best.best_disp_id')
            ->leftJoin('streelight_poles as pole', 'disp.streetlight_pole_id', '=', 'pole.id')
            ->leftJoin('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
            ->where('inv.project_id', $project->id)
            ->where('inv.store_id', $store->id)
            ->where('inv.item_code', '!=', 'SL04') // Exclude SL04 from export
            ->select(array_merge(
                [
                    'inv.item_code',
                    'inv.item',
                    'inv.serial_number',
                    DB::raw('COALESCE(inv.quantity, 0) as quantity'),
                    'inv.created_at',
                    'inv.received_date',
                    'disp.id as dispatch_id',
                    'disp.is_consumed',
                    'disp.streetlight_pole_id',
                    'disp.dispatch_date',
                    DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name'),
                ],
                $inventoryTable === 'inventory_streetlight'
                    ? [DB::raw('CASE WHEN inv.item_code = "SL02" THEN COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) ELSE NULL END as sim_number')]
                    : [DB::raw('NULL as sim_number')]
            ));

        // Apply filters (same as inventoryData)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('inv.item_code', 'like', "%{$search}%")
                    ->orWhere('inv.item', 'like', "%{$search}%")
                    ->orWhere('inv.serial_number', 'like', "%{$search}%")
                    ->orWhere(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        if ($request->filled('availability')) {
            $avail = $request->input('availability');
            if ($avail === 'In Stock') {
                $query->where('inv.quantity', '>', 0)->whereNull('disp.id');
            } elseif ($avail === 'Dispatched') {
                $query->whereNotNull('disp.id')
                    ->whereRaw('COALESCE(inv.quantity, 0) <= 0')
                    ->whereNull('disp.streetlight_pole_id');
            } elseif ($avail === 'Consumed') {
                $query->whereNotNull('disp.streetlight_pole_id');
            }
        }

        if ($request->filled('item_code')) {
            $query->where('inv.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $query->where(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        // Note: For very large datasets (100k+), standard export might fail due to memory.
        // Usually, chunking is recommended, but keeping existing structure as requested.
        $items = $query->orderBy('inv.created_at', 'desc')->get();

        $exportData = $items->map(function ($item) {
            $availability = 'In Stock';
            if (!empty($item->streetlight_pole_id)) {
                $availability = 'Consumed';
            } elseif ($item->dispatch_id) {
                $availability = 'Dispatched';
            } elseif (($item->quantity ?? 0) > 0) {
                $availability = 'In Stock';
            }

            $vendorName = trim($item->vendor_name ?? '') ?: '-';
            $dispatchDate = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $receivedDate = $item->received_date ? \Carbon\Carbon::parse($item->received_date)->format('d/m/Y') : ($item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-');
            $simNumber = (($item->item_code ?? '') === 'SL02' && trim((string)($item->sim_number ?? '')) !== '') ? $item->sim_number : '-';

            return [
                'Item Code' => $item->item_code,
                'Item' => $item->item,
                'Serial Number' => $item->serial_number,
                'SIM Number' => $simNumber,
                'Availability' => $availability,
                'Vendor' => $vendorName,
                'Dispatch Date' => $dispatchDate,
                'In Date' => $receivedDate,
            ];
        })->toArray();

        $date = now()->format('dmY');
        $hasFilters = $request->filled('availability') || $request->filled('item_code') || $request->filled('vendor_name');

        if ($hasFilters) {
            $storeName = strtoupper(substr($store->name ?? 'STORE', 0, 3));
            $parts = ['Inv', $storeName];

            if ($request->filled('availability')) {
                $avail = $request->input('availability');
                if ($avail === 'In Stock') $parts[] = 'INS';
                elseif ($avail === 'Dispatched') $parts[] = 'DIS';
                elseif ($avail === 'Consumed') $parts[] = 'CON';
            }

            if ($request->filled('vendor_name')) {
                $vendor = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $request->input('vendor_name')), 0, 3));
                $parts[] = $vendor ?: 'VEN';
            }

            if ($request->filled('item_code')) {
                $item = strtoupper(substr($request->input('item_code'), 0, 3));
                $parts[] = $item;
            }

            $parts[] = $date;
            $filename = implode('_', $parts) . '.xlsx';
        } else {
            $storeName = $store->name ?? 'Store';
            $filename = 'Inventory_' . $storeName . '_' . $date . '.xlsx';
        }

        return \App\Helpers\ExcelHelper::exportToExcel($exportData, $filename);
    }

    /**
     * Server-side pagination endpoint for dispatched items DataTable
     */
    public function dispatchedData(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;
        $user = auth()->user();
        $isAdmin = $user->role === UserRole::ADMIN->value;

        // Base Query
        $query = DB::table('inventory_dispatch as disp')
            ->join('users as vendor', 'disp.vendor_id', '=', 'vendor.id')
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
                DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) as vendor_name')
            );

        // Fast Total Count
        $recordsTotal = DB::table('inventory_dispatch')
            ->where('store_id', $store->id)
            ->where('project_id', $project->id)
            ->where('isDispatched', true)
            ->count();

        // Filtering
        $isFiltered = false;
        if ($request->filled('search.value')) {
            $isFiltered = true;
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('disp.item_code', 'like', "%{$search}%")
                    ->orWhere('disp.item', 'like', "%{$search}%")
                    ->orWhere('disp.serial_number', 'like', "%{$search}%")
                    ->orWhere(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$search}%");
            });
        }

        if ($request->filled('item_code')) {
            $isFiltered = true;
            $query->where('disp.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $isFiltered = true;
            $query->where(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), 'like', "%{$request->input('vendor_name')}%");
        }

        if ($request->filled('dispatch_date')) {
            $isFiltered = true;
            $query->whereDate('disp.dispatch_date', $request->input('dispatch_date'));
        }

        $recordsFiltered = $isFiltered ? $query->count() : $recordsTotal;

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', $isAdmin ? 5 : 4); // Default 'dispatch_date' index
        $orderDir = $request->input('order.0.dir', 'desc');

        // Map dataTable column index to DB columns
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:vendor, 5:date, 6:val, 7:act]
        // User:  [0:code, 1:item, 2:serial, 3:vendor, 4:date, 5:val, 6:act]

        $columnsAdmin = [
            1 => 'item_code', 2 => 'item', 3 => 'serial_number', 4 => 'vendor_name', 5 => 'dispatch_date', 6 => 'total_value'
        ];
        $columnsUser = [
            0 => 'item_code', 1 => 'item', 2 => 'serial_number', 3 => 'vendor_name', 4 => 'dispatch_date', 5 => 'total_value'
        ];

        $colMap = $isAdmin ? $columnsAdmin : $columnsUser;
        $sortCol = $colMap[$orderColumnIndex] ?? 'dispatch_date';

        if ($sortCol === 'vendor_name') {
            $query->orderBy(DB::raw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, ""))'), $orderDir);
        } else {
            $query->orderBy('disp.' . $sortCol, $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 50);

        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $items = $query->get();

        $data = $items->map(function ($item) use ($isAdmin) {
            $row = [];
            if ($isAdmin) {
                $row[] = '<input type="checkbox" class="row-checkbox" value="' . $item->id . '" data-id="' . $item->id . '">';
            }
            $row[] = $item->item_code ?? 'N/A';
            $row[] = $item->item ?? 'N/A';
            $row[] = $item->serial_number ?? 'N/A';
            $row[] = trim($item->vendor_name ?? '') ?: '-';
            $row[] = $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-';
            $row[] = '₹' . number_format($item->total_value ?? 0, 2);
            $row[] = '<button type="button" class="btn btn-sm btn-danger delete-item" data-id="' . $item->id . '" data-url="' . route('inventory.destroy', $item->id) . '"><i class="mdi mdi-delete"></i></button>';

            return $row;
        });

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
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

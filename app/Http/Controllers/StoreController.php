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
use App\Services\Logging\ActivityLogger;
use App\Http\Controllers\Concerns\HandlesColumnFilters;
use App\Support\StreetlightInventoryItems;
use App\Imports\MaterialTransferImport;
use App\Exports\MaterialTransferFormatExport;
use App\Models\InventroyStreetLightModel;
use App\Services\Inventory\InventoryHistoryService;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Warehouse / Store Management — each project has physical stores (warehouses) where inventory
 * items (Panels, Luminaries, Batteries, Structures) are received and tracked. This controller
 * handles store creation, viewing store inventory with real-time stock-vs-dispatched metrics,
 * server-side paginated DataTable for 100k+ inventory rows, and Excel export of filtered
 * inventory data.
 *
 * Data Flow:
 *   Admin creates Store → Inventory imported via GRN Excel → Store show page aggregates
 *   stock metrics (initial value, in-store, dispatched) → DataTable AJAX feeds paginated
 *   rows with dispatch status → Export streams filtered rows to Excel
 *
 * @depends-on Stores, Inventory, InventroyStreetLightModel, InventoryDispatch, InventoryExport, User, Project
 * @business-domain Inventory & Warehouse
 * @package App\Http\Controllers
 */
class StoreController extends Controller
{
    use HandlesColumnFilters;

    public function __construct(
        protected ActivityLogger $activityLogger,
        protected InventoryHistoryService $historyService
    ) {
    }

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

            $store = Stores::create([
                'project_id' => $projectId,
                'store_name' => $validated['name'],
                'address' => $validated['address'],
                'store_incharge_id' => $validated['storeIncharge'],
            ]);

            $this->activityLogger->log('store', 'created', $store, [
                'description' => "Created store '{$validated['name']}' for project #{$projectId}"
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
        $inventoryTotal = DB::table($inventoryTableName)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->count();
        Log::info('Inventory Total: ' . $inventoryTotal);

        // Each serial number is dispatched at most once per store/project, so a plain
        // SELECT is equivalent to the old GROUP_CONCAT+GROUP_BY but ~430x faster on
        // large stores (no filesort, no string-build/parse per group).
        $dispSubInit = DB::table('inventory_dispatch')
            ->select(
                DB::raw('id as best_disp_id'),
                'serial_number',
                'store_id',
                'project_id'
            )
            ->where('isDispatched', true)
            ->where('store_id', $store->id)
            ->where('project_id', $project->id);

        // OPTIMIZATION 2: Initial Data Load (Fast) - Only load exactly paginated rows
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
                ? [DB::raw('COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) as sim_number')]
                : [DB::raw('NULL as sim_number')]
            ))
            ->orderBy('inv.created_at', 'desc')
            // Initial paint only. Full dataset is served by server-side DataTables ajax.
            ->limit(config('crm.pagination.per_page', 50))
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
                ->where('project_id', $project->id)
                ->where('isDispatched', true)
                ->sum('total_value');

            $inStoreStockValue = max(0, (float) $initialStockValue - (float) $dispatchedStockValue);

            $statsData = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
                ->select(
                    'item_code',
                    DB::raw('MAX(item) as item_name'),
                    DB::raw('COUNT(*) as total_received'),
                    DB::raw('SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as current_stock')
                )
                ->groupBy('item_code')
                ->get();

            foreach ($statsData as $stat) {
                $total = (int) $stat->total_received;
                $stock = (int) $stat->current_stock;
                $itemStats[$stat->item_code] = [
                    'name' => trim(($stat->item_name ?: 'Item') . ' (' . $stat->item_code . ')'),
                    'total' => $total,
                    'in_stock' => $stock,
                    'dispatched' => $total - $stock,
                ];
            }

            // Calculate total quantities for pie chart.
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

        $itemCodes = DB::table($inventoryTableName)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->distinct()
            ->pluck('item_code');

        $inventoryItems = collect([]);
        if ($project->project_type == 1) {
            $inventoryItems = DB::table($inventoryTableName)
                ->where('project_id', $project->id)
                ->where('store_id', $store->id)
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

        $otherStores = Stores::where('project_id', $project->id)
            ->where('id', '!=', $store->id)
            ->get(['id', 'store_name']);

        return view('stores.show', compact(
            'store',
            'project',
            'otherStores',
            'unifiedInventory',
            'inventoryTotal',
            'initialStockValue',
            'inStoreStockValue',
            'dispatchedStockValue',
            'inStoreStockQuantity',
            'dispatchedStockQuantity',
            'itemStats',
            'users',
            'assignedVendors',
            'inventoryItems',
            'isAdmin',
            'itemCodes',
            'inStock',
            'dispatched',
            'inventoryModel'
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
            if (!$hasAccess)
                return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        // Each serial number is dispatched at most once per store/project — plain SELECT
        // avoids the GROUP BY + GROUP_CONCAT filesort (see show() for full explanation).
        $dispSub = DB::table('inventory_dispatch')
            ->select(
                DB::raw('id as best_disp_id'),
                'serial_number',
                'store_id',
                'project_id'
            )
            ->where('isDispatched', true)
            ->where('store_id', $store->id)
            ->where('project_id', $project->id);

        // 1. Base Query Structure - join to single dispatch per inv to prevent duplicate rows on sort
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
                ? [DB::raw('COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) as sim_number')]
                : [DB::raw('NULL as sim_number')]
            ));

        // 2. Count Total (Fastest way - No Joins for total count)
        $recordsTotal = DB::table($inventoryTable)
            ->where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->count();

        // 3. Filtering
        $isFiltered = false;

        // Global Search
        if ($request->filled('search.value')) {
            $isFiltered = true;
            $search = $request->input('search.value');
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('inv.item_code', 'like', $searchTerm)
                    ->orWhere('inv.item', 'like', $searchTerm)
                    ->orWhere('inv.serial_number', 'like', $searchTerm)
                    ->orWhereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', [$searchTerm]);
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
            $query->whereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', ['%' . $request->input('vendor_name') . '%']);
        }

        // ── Per-column filters from the SAP-style filter popup ──────────────
        // Admin DOM layout: 0=checkbox, 1=Item Code, 2=Item, 3=Serial, 4=SIM,
        //                   5=Availability (computed, skip), 6=Vendor (raw expr),
        //                   7=Dispatch Date, 8=In Date, 9=Actions
        // User  DOM layout: 0=Item Code, 1=Item, 2=Serial, 3=SIM,
        //                   4=Availability (skip), 5=Vendor (raw expr),
        //                   6=Dispatch Date, 7=In Date, 8=Actions
        $columnFilters = $this->parseColumnFilters($request);
        if ($columnFilters) {
            $cfColMap = $isAdmin ? [
                1 => 'inv.item_code',
                2 => 'inv.item',
                3 => 'inv.serial_number',
                4 => 'inv.sim_number',
                // 5 = availability (computed from dispatch state — skip generic handling)
                // 6 = vendor_name (handled below via raw expr)
                7 => 'disp.dispatch_date',
                8 => 'inv.received_date',
            ] : [
                0 => 'inv.item_code',
                1 => 'inv.item',
                2 => 'inv.serial_number',
                3 => 'inv.sim_number',
                // 4 = availability (skip)
                // 5 = vendor_name (handled below via raw expr)
                6 => 'disp.dispatch_date',
                7 => 'inv.received_date',
            ];

            if ($this->applyColumnFilters($query, $columnFilters, $cfColMap)) {
                $isFiltered = true;
            }

            // vendor_name is a computed expression — handle it separately
            $vendorColIdx = $isAdmin ? 6 : 5;
            if (isset($columnFilters[$vendorColIdx])) {
                $vendorExpr = "CONCAT(COALESCE(vendor.firstName,''),' ',COALESCE(vendor.lastName,''))";
                if ($this->applyColumnFilterRaw($query, $vendorExpr, $columnFilters[$vendorColIdx])) {
                    $isFiltered = true;
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // 4. Count Filtered (Only run expensive count if filtered)
        $recordsFiltered = $isFiltered ? $query->count() : $recordsTotal;

        // 5. Ordering
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:sim, 5:avail, 6:vendor, 7:disp_date, 8:in_date, 9:act]
        // User:  [0:code, 1:item, 2:serial, 3:sim, 4:avail, 5:vendor, 6:disp_date, 7:in_date, 8:act]
        $orderColumnIndex = $request->input('order.0.column', $isAdmin ? 8 : 7); // Default 'created_at' index
        $orderDir = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc']) ? $request->input('order.0.dir', 'desc') : 'desc';

        // Map dataTable column index to DB columns
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:sim, 5:avail, 6:vendor, 7:disp_date, 8:in_date, 9:act]
        // User:  [0:code, 1:item, 2:serial, 3:sim, 4:avail, 5:vendor, 6:disp_date, 7:in_date, 8:act]

        $columnsAdmin = [
            1 => 'item_code',
            2 => 'item',
            3 => 'serial_number',
            4 => 'sim_number',
            5 => 'availability',
            6 => 'vendor_name',
            7 => 'dispatch_date',
            8 => 'created_at'
        ];
        $columnsUser = [
            0 => 'item_code',
            1 => 'item',
            2 => 'serial_number',
            3 => 'sim_number',
            4 => 'availability',
            5 => 'vendor_name',
            6 => 'dispatch_date',
            7 => 'created_at'
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

            $simCell = (StreetlightInventoryItems::isLuminary($item->item ?? null, $item->item_code ?? null) && trim((string) ($item->sim_number ?? '')) !== '')
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
                $actions .= '<form action="' . route('inventory.return') . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to return this item?\');">' . csrf_field() . '<input type="hidden" name="serial_number" value="' . $item->serial_number . '"><button type="submit" class="btn btn-sm btn-warning" title="Return"><i class="mdi mdi-undo"></i></button></form>';
            } elseif ($availability === 'Consumed') {
                $actions .= '<button type="button" class="btn btn-sm btn-primary replace-item" data-dispatch-id="' . ($item->dispatch_id ?? '') . '" data-serial-number="' . $item->serial_number . '" title="Replace"><i class="mdi mdi-swap-horizontal"></i></button>';
            }
            $actions .= ' <button type="button" class="btn btn-sm btn-outline-secondary view-history" data-serial="' . e($item->serial_number) . '" title="View History"><i class="mdi mdi-history"></i></button>';
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
    public function exportInventory(\App\Http\Requests\DataTableExportRequest $request, $storeId)
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
                ? [DB::raw('COALESCE(NULLIF(TRIM(inv.sim_number), ""), NULLIF(TRIM(pole.sim_number), "")) as sim_number')]
                : [DB::raw('NULL as sim_number')]
            ));

        // Apply filters (same as inventoryData)
        $searchValue = $request->input('search.value') ?: $request->input('search');
        if ($searchValue) {
            $search = $searchValue;
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('inv.item_code', 'like', $searchTerm)
                    ->orWhere('inv.item', 'like', $searchTerm)
                    ->orWhere('inv.serial_number', 'like', $searchTerm)
                    ->orWhereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', [$searchTerm]);
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
            $query->whereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', ['%' . $request->input('vendor_name') . '%']);
        }

        $exportService = app(\App\Services\Export\DataTableExportService::class);
        $query = $exportService->applyExportScope($query, $request);
        $exportService->assertWithinRowLimit($query);

        $query->orderBy('inv.created_at', 'desc');

        $date = now()->format('dmY');
        $hasFilters = $request->filled('availability') || $request->filled('item_code') || $request->filled('vendor_name');

        if ($hasFilters) {
            $storeName = strtoupper(substr($store->name ?? 'STORE', 0, 3));
            $parts = ['Inv', $storeName];

            if ($request->filled('availability')) {
                $avail = $request->input('availability');
                if ($avail === 'In Stock')
                    $parts[] = 'INS';
                elseif ($avail === 'Dispatched')
                    $parts[] = 'DIS';
                elseif ($avail === 'Consumed')
                    $parts[] = 'CON';
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

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventoryExport($query), $filename);
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
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('disp.item_code', 'like', $searchTerm)
                    ->orWhere('disp.item', 'like', $searchTerm)
                    ->orWhere('disp.serial_number', 'like', $searchTerm)
                    ->orWhereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', [$searchTerm]);
            });
        }

        if ($request->filled('item_code')) {
            $isFiltered = true;
            $query->where('disp.item_code', $request->input('item_code'));
        }

        if ($request->filled('vendor_name')) {
            $isFiltered = true;
            $query->whereRaw('CONCAT(COALESCE(vendor.firstName, ""), " ", COALESCE(vendor.lastName, "")) LIKE ?', ['%' . $request->input('vendor_name') . '%']);
        }

        if ($request->filled('dispatch_date')) {
            $isFiltered = true;
            $query->whereDate('disp.dispatch_date', $request->input('dispatch_date'));
        }

        // ── Per-column filters from the SAP-style filter popup ──────────────
        // Admin DOM layout: 0=checkbox, 1=Item Code, 2=Item, 3=Serial,
        //                   4=Vendor (raw expr), 5=Dispatch Date, 6=Value, 7=Actions
        // User  DOM layout: 0=Item Code, 1=Item, 2=Serial,
        //                   3=Vendor (raw expr), 4=Dispatch Date, 5=Value, 6=Actions
        $columnFilters = $this->parseColumnFilters($request);
        if ($columnFilters) {
            $cfColMap = $isAdmin ? [
                1 => 'disp.item_code',
                2 => 'disp.item',
                3 => 'disp.serial_number',
                // 4 = vendor_name (handled below via raw expr)
                5 => 'disp.dispatch_date',
                6 => 'disp.total_value',
            ] : [
                0 => 'disp.item_code',
                1 => 'disp.item',
                2 => 'disp.serial_number',
                // 3 = vendor_name (handled below)
                4 => 'disp.dispatch_date',
                5 => 'disp.total_value',
            ];

            if ($this->applyColumnFilters($query, $columnFilters, $cfColMap)) {
                $isFiltered = true;
            }

            // vendor_name is a computed expression — handle separately
            $vendorColIdx = $isAdmin ? 4 : 3;
            if (isset($columnFilters[$vendorColIdx])) {
                $vendorExpr = "CONCAT(COALESCE(vendor.firstName,''),' ',COALESCE(vendor.lastName,''))";
                if ($this->applyColumnFilterRaw($query, $vendorExpr, $columnFilters[$vendorColIdx])) {
                    $isFiltered = true;
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $recordsFiltered = $isFiltered ? $query->count() : $recordsTotal;

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', $isAdmin ? 5 : 4); // Default 'dispatch_date' index
        $orderDir = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc']) ? $request->input('order.0.dir', 'desc') : 'desc';

        // Map dataTable column index to DB columns
        // Admin: [0:chk, 1:code, 2:item, 3:serial, 4:vendor, 5:date, 6:val, 7:act]
        // User:  [0:code, 1:item, 2:serial, 3:vendor, 4:date, 5:val, 6:act]

        $columnsAdmin = [
            1 => 'item_code',
            2 => 'item',
            3 => 'serial_number',
            4 => 'vendor_name',
            5 => 'dispatch_date',
            6 => 'total_value'
        ];
        $columnsUser = [
            0 => 'item_code',
            1 => 'item',
            2 => 'serial_number',
            3 => 'vendor_name',
            4 => 'dispatch_date',
            5 => 'total_value'
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
     * Return the full lifecycle history for a single inventory serial number.
     * Used by the inventory history offcanvas panel in stores/show.blade.php.
     */
    public function inventoryHistory(Request $request, $storeId, $serial)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $user = auth()->user();
        if ($user->role === UserRole::PROJECT_MANAGER->value) {
            $hasAccess = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAccess) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // All history entries for this serial in this store, oldest first
        $history = DB::table('inventory_history as ih')
            ->leftJoin('users as actor', 'actor.id', '=', 'ih.user_id')
            ->where('ih.serial_number', $serial)
            ->where('ih.store_id', $store->id)
            ->select(
                'ih.id', 'ih.action', 'ih.quantity_before', 'ih.quantity_after',
                'ih.metadata', 'ih.created_at',
                DB::raw('TRIM(CONCAT(COALESCE(actor.firstName,"")," ",COALESCE(actor.lastName,""))) as actor_name')
            )
            ->orderBy('ih.created_at', 'asc')
            ->get();

        // Batch-resolve vendor names from metadata (avoid N+1)
        $vendorIds = $history->map(function ($e) {
            $m = json_decode($e->metadata ?? '{}', true) ?? [];
            return $m['vendor_id'] ?? null;
        })->filter()->unique()->values();

        $vendors = $vendorIds->isNotEmpty()
            ? DB::table('users')
                ->whereIn('id', $vendorIds)
                ->pluck(DB::raw("TRIM(CONCAT(COALESCE(firstName,''),' ',COALESCE(lastName,'')))"), 'id')
            : collect();

        // Enrich each entry with pre-decoded fields for the frontend
        $enriched = $history->map(function ($entry) use ($vendors) {
            $m = json_decode($entry->metadata ?? '{}', true) ?? [];
            $entry->vendor_name          = isset($m['vendor_id']) ? ($vendors[$m['vendor_id']] ?? null) : null;
            $entry->complete_pole_number = $m['complete_pole_number'] ?? null;
            $entry->old_serial           = $m['old_serial_number'] ?? null;
            $entry->new_serial           = $m['new_serial_number'] ?? null;
            $entry->swapped_with         = $m['swapped_with'] ?? null;
            $entry->swap_type            = $m['swap_type'] ?? null;
            unset($entry->metadata);
            return $entry;
        });

        // Current state for the header panel
        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';
        $item = DB::table($inventoryTable . ' as inv')
            ->leftJoin('inventory_dispatch as d', function ($j) {
                $j->on('d.serial_number', '=', 'inv.serial_number')->where('d.isDispatched', true);
            })
            ->leftJoin('users as v', 'v.id', '=', 'd.vendor_id')
            ->leftJoin('streelight_poles as pole', 'pole.id', '=', 'd.streetlight_pole_id')
            ->where('inv.serial_number', $serial)
            ->where('inv.store_id', $store->id)
            ->select(
                'inv.item_code', 'inv.item', 'inv.serial_number', 'inv.quantity',
                'd.id as dispatch_id', 'd.streetlight_pole_id', 'd.dispatch_date',
                'pole.complete_pole_number as current_pole',
                DB::raw('TRIM(CONCAT(COALESCE(v.firstName,"")," ",COALESCE(v.lastName,""))) as current_vendor')
            )
            ->first();

        return response()->json([
            'store_name' => $store->store_name,
            'item'       => $item,
            'history'    => $enriched,
        ]);
    }

    /**
     * Download a sample Excel template for material transfer upload.
     */
    public function transferSample()
    {
        return Excel::download(new MaterialTransferFormatExport(), 'material_transfer_template.xlsx');
    }

    /**
     * Preview material transfer: parse Excel, validate each serial, return valid/skipped lists.
     */
    public function transferPreview(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        $user = auth()->user();
        $isAdmin = $user->role === UserRole::ADMIN->value;
        $isPM    = $user->role === UserRole::PROJECT_MANAGER->value;

        if (!$isAdmin && !$isPM) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($isPM) {
            $hasAccess = DB::table('project_user')
                ->where('project_id', $project->id)->where('user_id', $user->id)->exists();
            if (!$hasAccess) return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file'                 => 'required|file|mimes:xlsx,xls,csv',
            'destination_store_id' => 'required|integer',
        ]);

        $destStoreId = (int) $request->destination_store_id;
        if ($destStoreId === (int) $storeId) {
            return response()->json(['error' => 'Destination store must differ from the source store.'], 422);
        }

        $destStore = Stores::where('id', $destStoreId)->where('project_id', $project->id)->first();
        if (!$destStore) {
            return response()->json(['error' => 'Destination store not found in this project.'], 422);
        }

        $collection = Excel::toCollection(new MaterialTransferImport(), $request->file('file'))->first();

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        $valid   = [];
        $skipped = [];
        $seen    = [];

        foreach ($collection as $row) {
            $serial = trim((string) ($row['serial_number'] ?? $row['serialnumber'] ?? $row['serial'] ?? ''));
            if ($serial === '') continue;

            if (isset($seen[$serial])) {
                $skipped[] = ['serial' => $serial, 'reason' => 'Duplicate in file'];
                continue;
            }
            $seen[$serial] = true;

            $item = DB::table($inventoryTable)
                ->where('serial_number', $serial)
                ->where('store_id', $storeId)
                ->where('project_id', $project->id)
                ->first();

            if (!$item) {
                $skipped[] = ['serial' => $serial, 'reason' => 'Not found in this store'];
                continue;
            }

            $activeDispatch = DB::table('inventory_dispatch')
                ->where('serial_number', $serial)
                ->where('store_id', $storeId)
                ->where('isDispatched', true)
                ->first();

            if ($activeDispatch) {
                $reason = $activeDispatch->is_consumed ? 'Already consumed (on a pole)' : 'Already dispatched to vendor';
                $skipped[] = ['serial' => $serial, 'reason' => $reason, 'item' => $item->item, 'item_code' => $item->item_code];
                continue;
            }

            $valid[] = ['serial' => $serial, 'item' => $item->item, 'item_code' => $item->item_code, 'id' => $item->id];
        }

        return response()->json([
            'valid'            => $valid,
            'skipped'          => $skipped,
            'destination_store' => $destStore->store_name,
        ]);
    }

    /**
     * Confirm and execute material transfer for a list of serials to another store.
     */
    public function transferConfirm(Request $request, $storeId)
    {
        $store = Stores::with('project')->findOrFail($storeId);
        $project = $store->project;
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        $user = auth()->user();
        $isAdmin = $user->role === UserRole::ADMIN->value;
        $isPM    = $user->role === UserRole::PROJECT_MANAGER->value;

        if (!$isAdmin && !$isPM) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($isPM) {
            $hasAccess = DB::table('project_user')
                ->where('project_id', $project->id)->where('user_id', $user->id)->exists();
            if (!$hasAccess) return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'destination_store_id' => 'required|integer',
            'serials'              => 'required|array|min:1',
            'serials.*'            => 'required|string',
        ]);

        $destStoreId = (int) $request->destination_store_id;
        if ($destStoreId === (int) $storeId) {
            return response()->json(['error' => 'Destination store must differ from source.'], 422);
        }

        $destStore = Stores::where('id', $destStoreId)->where('project_id', $project->id)->first();
        if (!$destStore) {
            return response()->json(['error' => 'Destination store not found in this project.'], 422);
        }

        $inventoryTable = ($project->project_type == 1) ? 'inventory_streetlight' : 'inventory';

        $transferred = [];
        $failed      = [];

        DB::transaction(function () use (
            $request, $storeId, $destStoreId, $inventoryTable, $project,
            $store, $destStore, $user, &$transferred, &$failed
        ) {
            foreach ($request->serials as $serial) {
                $item = DB::table($inventoryTable)
                    ->where('serial_number', $serial)
                    ->where('store_id', $storeId)
                    ->where('project_id', $project->id)
                    ->first();

                if (!$item) {
                    $failed[] = ['serial' => $serial, 'reason' => 'Not found in source store'];
                    continue;
                }

                $activeDispatch = DB::table('inventory_dispatch')
                    ->where('serial_number', $serial)
                    ->where('store_id', $storeId)
                    ->where('isDispatched', true)
                    ->first();

                if ($activeDispatch) {
                    $failed[] = ['serial' => $serial, 'item' => $item->item, 'reason' => 'Item is dispatched or consumed'];
                    continue;
                }

                DB::table($inventoryTable)
                    ->where('serial_number', $serial)
                    ->where('store_id', $storeId)
                    ->update(['store_id' => $destStoreId]);

                $this->historyService->log('transferred', [
                    'inventory_id'    => $item->id,
                    'inventory_type'  => 'streetlight',
                    'user_id'         => $user->id,
                    'project_id'      => $project->id,
                    'store_id'        => $storeId,
                    'quantity_before' => 1,
                    'quantity_after'  => 1,
                    'serial_number'   => $serial,
                    'metadata'        => json_encode([
                        'from_store_id'   => (int) $storeId,
                        'from_store_name' => $store->store_name,
                        'to_store_id'     => $destStoreId,
                        'to_store_name'   => $destStore->store_name,
                    ]),
                ]);

                $transferred[] = ['serial' => $serial, 'item' => $item->item];
            }
        });

        return response()->json(['transferred' => $transferred, 'failed' => $failed]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $store = Stores::findOrFail($id);
            $storeName = $store->store_name;
            $store->delete();

            $this->activityLogger->log('store', 'deleted', null, [
                'description' => "Deleted store '{$storeName}'"
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

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

        $inStock = $inventoryModel::where('project_id', $project->id)
            ->where('store_id', $store->id)
            ->where('quantity', '>', 0)
            ->select('id', 'item_code', 'item', 'serial_number', 'quantity', 'rate', 'total_value', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Optimized: Only get aggregated dispatched data, not all records
        $dispatchedAggregated = \App\Models\InventoryDispatch::where('store_id', $store->id)
            ->where('isDispatched', true)
            ->whereNotNull('item_code')
            ->select('item_code', \Illuminate\Support\Facades\DB::raw('SUM(total_quantity) as total_quantity'), \Illuminate\Support\Facades\DB::raw('SUM(total_value) as total_value'))
            ->groupBy('item_code')
            ->get()
            ->keyBy('item_code');

        // Get dispatched items for display - Eager load vendor to prevent N+1
        $dispatched = \App\Models\InventoryDispatch::where('store_id', $store->id)
            ->where('isDispatched', true)
            ->whereNotNull('item_code')
            ->with('vendor:id,name') // Eager load vendor to prevent N+1 queries
            ->select('id', 'item_code', 'item', 'serial_number', 'vendor_id', 'dispatch_date', 'total_value', 'created_at')
            ->orderBy('dispatch_date', 'desc')
            ->get();

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

        // Get item-wise statistics - Optimized: Single query instead of loop
        $itemStats = [];
        if ($project->project_type == 1) {
            try {
                $items = ['SL01' => 'Panel', 'SL02' => 'Luminary', 'SL03' => 'Battery', 'SL04' => 'Structure'];

                // Single query to get all item totals grouped by item_code
                $inventoryTotals = $inventoryModel::where('project_id', $project->id)
                    ->where('store_id', $store->id)
                    ->whereIn('item_code', array_keys($items))
                    ->select(
                        'item_code',
                        \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_quantity')
                    )
                    ->groupBy('item_code')
                    ->get()
                    ->keyBy('item_code');

                foreach ($items as $code => $name) {
                    try {
                        $total = isset($inventoryTotals[$code]) && is_numeric($inventoryTotals[$code]->total_quantity)
                            ? (float) $inventoryTotals[$code]->total_quantity
                            : 0;

                        // Use pre-aggregated dispatched data instead of filtering collection
                        $dispatchedCount = isset($dispatchedAggregated[$code]) && is_numeric($dispatchedAggregated[$code]->total_quantity)
                            ? (float) $dispatchedAggregated[$code]->total_quantity
                            : 0;

                        $inStockCount = max(0, (float) $total - (float) $dispatchedCount);

                        $itemStats[$code] = [
                            'name' => $name,
                            'total' => (float) $total,
                            'dispatched' => (float) $dispatchedCount,
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

        return view('stores.show', compact('store', 'project', 'inStock', 'dispatched', 'initialStockValue', 'inStoreStockValue', 'dispatchedStockValue', 'itemStats', 'users', 'inventoryModel', 'assignedVendors', 'inventoryItems'));
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

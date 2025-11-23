<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardServiceInterface;
use App\Contracts\AnalyticsServiceInterface;
use App\Enums\UserRole;
use App\Models\{Project, Site, Streetlight, Task, StreetlightTask, User, Inventory, InventoryDispatch};
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Service
 * 
 * Centralized dashboard data preparation with role-based customization
 */
class DashboardService extends BaseService implements DashboardServiceInterface
{
    public function __construct(
        protected AnalyticsServiceInterface $analytics
    ) {
    }

    /**
     * Get dashboard data based on user role
     */
    public function getDashboardData(int $userId, string $userRole, array $filters = []): array
    {
        $cacheKey = "dashboard:{$userId}:{$userRole}:" . md5(json_encode($filters));

        return Cache::remember($cacheKey, 900, function () use ($userId, $userRole, $filters) {
            $role = is_numeric($userRole) ? UserRole::from((int) $userRole) : null;

            return match ($userRole) {
                '0', 'Administrator' => $this->getAdminDashboard($filters),
                '1', 'Site Engineer' => $this->getSiteEngineerDashboard($userId, $filters),
                '2', 'Project Manager' => $this->getProjectManagerDashboard($userId, $filters),
                '3', 'Vendor' => $this->getVendorDashboard($userId, $filters),
                '4', 'Store Incharge' => $this->getStoreInchargeDashboard($userId, $filters),
                default => $this->getBasicDashboard($userId, $filters),
            };
        });
    }

    /**
     * Admin dashboard with complete overview
     */
    public function getAdminDashboard(array $filters = []): array
    {
        return Cache::remember('dashboard:admin:' . md5(json_encode($filters)), 900, function () use ($filters) {
            return [
                'projects' => [
                    'total' => Project::count(),
                    'rooftop' => Project::where('project_type', 'Rooftop Solar')->count(),
                    'streetlight' => Project::where('project_type', 'Streetlight')->count(),
                ],
                'sites' => [
                    'rooftop' => Site::count(),
                    'streetlight' => Streetlight::count(),
                ],
                'users' => [
                    'total' => User::count(),
                    'engineers' => User::where('role', '1')->count(),
                    'vendors' => User::where('role', '3')->count(),
                ],
                'tasks' => [
                    'total' => Task::count() + StreetlightTask::count(),
                    'pending' => Task::where('status', 'Pending')->count() + StreetlightTask::where('status', 'Pending')->count(),
                    'completed' => Task::where('status', 'Completed')->count() + StreetlightTask::where('status', 'Completed')->count(),
                ],
                'inventory' => [
                    'total_value' => Inventory::sum(DB::raw('quantityStock * rate')),
                    'items_count' => Inventory::count(),
                    'dispatched' => InventoryDispatch::where('is_consumed', true)->count(),
                ],
                'recent_activities' => $this->getRecentActivities(10),
            ];
        });
    }

    /**
     * Project Manager dashboard
     */
    public function getProjectManagerDashboard(int $userId, array $filters = []): array
    {
        return Cache::remember("dashboard:pm:{$userId}", 900, function () use ($userId) {
            $projects = Project::whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->get();

            $projectIds = $projects->pluck('id');

            return [
                'my_projects' => [
                    'total' => $projects->count(),
                    'active' => $projects->filter(fn($p) => !in_array($p->status ?? '', ['Completed', 'Closed']))->count(),
                ],
                'sites' => [
                    'total' => Site::whereIn('project_id', $projectIds)->count(),
                    'pending' => Site::whereIn('project_id', $projectIds)->whereNull('commissioning_date')->count(),
                ],
                'tasks' => [
                    'total' => Task::whereIn('project_id', $projectIds)->count(),
                    'in_progress' => Task::whereIn('project_id', $projectIds)->where('status', 'In Progress')->count(),
                ],
                'team' => [
                    'engineers' => User::where('role', '1')->whereIn('project_id', $projectIds)->count(),
                    'vendors' => User::where('role', '3')->whereIn('project_id', $projectIds)->count(),
                ],
                'projects_list' => $projects->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->project_name,
                    'type' => $p->project_type,
                    'progress' => $this->calculateProjectProgress($p),
                ]),
            ];
        });
    }

    /**
     * Site Engineer dashboard
     */
    public function getSiteEngineerDashboard(int $userId, array $filters = []): array
    {
        return Cache::remember("dashboard:engineer:{$userId}", 600, function () use ($userId) {
            return [
                'my_tasks' => [
                    'total' => Task::where('engineer_id', $userId)->count() + StreetlightTask::where('engineer_id', $userId)->count(),
                    'pending' => Task::where('engineer_id', $userId)->where('status', 'Pending')->count(),
                    'in_progress' => Task::where('engineer_id', $userId)->where('status', 'In Progress')->count(),
                    'completed_today' => Task::where('engineer_id', $userId)->where('status', 'Completed')->whereDate('updated_at', today())->count(),
                ],
                'my_sites' => [
                    'total' => Site::where('site_engineer', $userId)->count(),
                    'pending' => Site::where('site_engineer', $userId)->whereNull('commissioning_date')->count(),
                ],
                'vendors' => [
                    'assigned' => User::where('role', '3')->where('engineer_id', $userId)->count(),
                ],
                'pending_tasks' => Task::where('engineer_id', $userId)
                    ->whereIn('status', ['Pending', 'In Progress'])
                    ->with(['project', 'site'])
                    ->orderBy('start_date', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(fn($t) => [
                        'id' => $t->id,
                        'name' => $t->task_name ?? $t->activity,
                        'project' => $t->project?->project_name,
                        'status' => $t->status,
                        'due_date' => $t->end_date,
                    ]),
            ];
        });
    }

    /**
     * Vendor dashboard
     */
    public function getVendorDashboard(int $userId, array $filters = []): array
    {
        return Cache::remember("dashboard:vendor:{$userId}", 600, function () use ($userId) {
            return [
                'my_tasks' => [
                    'total' => Task::where('vendor_id', $userId)->count() + StreetlightTask::where('vendor_id', $userId)->count(),
                    'pending' => Task::where('vendor_id', $userId)->where('status', 'Pending')->count(),
                    'completed' => Task::where('vendor_id', $userId)->where('status', 'Completed')->count(),
                ],
                'materials' => [
                    'dispatched' => InventoryDispatch::where('vendor_id', $userId)->count(),
                    'consumed' => InventoryDispatch::where('vendor_id', $userId)->where('is_consumed', true)->count(),
                ],
                'tasks_list' => Task::where('vendor_id', $userId)
                    ->with(['project', 'engineer'])
                    ->orderBy('start_date', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($t) => [
                        'id' => $t->id,
                        'name' => $t->task_name ?? $t->activity,
                        'project' => $t->project?->project_name,
                        'engineer' => $t->engineer?->name,
                        'status' => $t->status,
                    ]),
            ];
        });
    }

    /**
     * Store Incharge dashboard
     */
    public function getStoreInchargeDashboard(int $userId, array $filters = []): array
    {
        return Cache::remember("dashboard:store:{$userId}", 900, function () {
            return [
                'inventory' => [
                    'total_items' => Inventory::count(),
                    'total_value' => Inventory::sum(DB::raw('quantityStock * rate')),
                    'low_stock' => Inventory::where('quantityStock', '<', 10)->count(),
                ],
                'dispatch' => [
                    'today' => InventoryDispatch::whereDate('created_at', today())->count(),
                    'pending_consumption' => InventoryDispatch::where('is_consumed', false)->count(),
                    'total_dispatched' => InventoryDispatch::sum('quantity'),
                ],
                'recent_dispatches' => InventoryDispatch::with(['project', 'vendor'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn($d) => [
                        'id' => $d->id,
                        'item' => $d->product_name ?? $d->item_code,
                        'quantity' => $d->quantity,
                        'project' => $d->project?->project_name,
                        'vendor' => $d->vendor?->name,
                        'date' => $d->created_at->format('Y-m-d'),
                    ]),
            ];
        });
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities(int $limit = 10): array
    {
        $activities = collect();

        // Recent tasks
        $recentTasks = Task::with(['engineer', 'project'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'type' => 'task',
                'description' => "Task '{$t->task_name}' updated",
                'user' => $t->engineer?->name,
                'timestamp' => $t->updated_at,
            ]);

        $activities = $activities->merge($recentTasks);

        return $activities->sortByDesc('timestamp')->take($limit)->values()->toArray();
    }

    /**
     * Calculate project progress
     */
    protected function calculateProjectProgress($project): int
    {
        if ($project->project_type === 'Streetlight') {
            $totalPoles = Streetlight::where('project_id', $project->id)->count();
            if ($totalPoles === 0)
                return 0;

            $completedTasks = StreetlightTask::where('project_id', $project->id)
                ->where('status', 'Completed')
                ->count();

            return (int) (($completedTasks / $totalPoles) * 100);
        }

        // Rooftop
        $totalSites = Site::where('project_id', $project->id)->count();
        if ($totalSites === 0)
            return 0;

        $completedSites = Site::where('project_id', $project->id)
            ->whereNotNull('commissioning_date')
            ->count();

        return (int) (($completedSites / $totalSites) * 100);
    }

    /**
     * Basic dashboard fallback
     */
    protected function getBasicDashboard(int $userId, array $filters = []): array
    {
        return [
            'message' => 'Dashboard data available',
            'user_id' => $userId,
        ];
    }
}

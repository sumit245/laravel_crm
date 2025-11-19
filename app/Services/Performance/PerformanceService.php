<?php

namespace App\Services\Performance;

use App\Contracts\PerformanceServiceInterface;
use App\Models\{User, Task, Project, Pole, StreetlightTask};
use Illuminate\Support\Facades\{Cache, DB};
use Carbon\Carbon;

class PerformanceService implements PerformanceServiceInterface
{
    private const CACHE_TTL = 900; // 15 minutes

    /**
     * Get hierarchical performance data based on user role.
     */
    public function getHierarchicalPerformance(int $userId, int $userRole, int $projectId, array $filters = []): array
    {
        $cacheKey = "performance.hierarchical.{$userId}.{$projectId}." . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $userRole, $projectId, $filters) {
            $project = Project::findOrFail($projectId);
            $isStreetlight = $project->project_type == 1;

            return match($userRole) {
                0 => $this->getAdminHierarchy($projectId, $isStreetlight, $filters), // Admin
                2 => $this->getManagerHierarchy($userId, $projectId, $isStreetlight, $filters), // Project Manager
                1 => $this->getEngineerHierarchy($userId, $projectId, $isStreetlight, $filters), // Site Engineer
                default => []
            };
        });
    }

    /**
     * Admin sees all Project Managers with their subordinates.
     */
    private function getAdminHierarchy(int $projectId, bool $isStreetlight, array $filters): array
    {
        // Get managers either directly assigned to project OR those who have tasks in the project
        $managers = User::where('role', 2)
            ->where(function($query) use ($projectId, $isStreetlight) {
                $query->where('project_id', $projectId)
                    ->orWhereHas($isStreetlight ? 'streetlightTasks' : 'managerTasks', function($q) use ($projectId) {
                        $q->where('project_id', $projectId);
                    });
            })
            ->distinct()
            ->get();

        return $managers->map(function ($manager) use ($projectId, $isStreetlight, $filters) {
            $metrics = $this->calculateUserMetrics($manager->id, $projectId, $isStreetlight, $filters, 2);
            
            return [
                'user' => $manager,
                'metrics' => $metrics,
                'subordinates' => [
                    'engineers' => $this->getEngineersByManager($manager->id, $projectId, $isStreetlight, $filters),
                    'vendors' => $this->getVendorsByManager($manager->id, $projectId, $isStreetlight, $filters),
                ]
            ];
        })->sortByDesc('metrics.performance_percentage')->values()->toArray();
    }

    /**
     * Project Manager sees their Site Engineers and Vendors.
     */
    private function getManagerHierarchy(int $managerId, int $projectId, bool $isStreetlight, array $filters): array
    {
        return [
            'engineers' => $this->getEngineersByManager($managerId, $projectId, $isStreetlight, $filters),
            'vendors' => $this->getVendorsByManager($managerId, $projectId, $isStreetlight, $filters),
        ];
    }

    /**
     * Site Engineer sees their Vendors.
     */
    private function getEngineerHierarchy(int $engineerId, int $projectId, bool $isStreetlight, array $filters): array
    {
        // Get vendors either by site_engineer_id relationship OR those who have tasks under this engineer
        $vendors = User::where('role', 3)
            ->where(function($query) use ($engineerId, $projectId, $isStreetlight) {
                $query->where(function($q) use ($engineerId, $projectId) {
                    $q->where('site_engineer_id', $engineerId)
                      ->where('project_id', $projectId);
                })
                ->orWhereHas($isStreetlight ? 'streetlightVendorTasks' : 'vendorTasks', function($q) use ($engineerId, $projectId) {
                    $q->where('engineer_id', $engineerId)
                      ->where('project_id', $projectId);
                });
            })
            ->distinct()
            ->get();

        return [
            'vendors' => $vendors->map(function ($vendor) use ($projectId, $isStreetlight, $filters) {
                return [
                    'user' => $vendor,
                    'metrics' => $this->calculateUserMetrics($vendor->id, $projectId, $isStreetlight, $filters, 3),
                ];
            })->sortByDesc('metrics.performance_percentage')->values()->toArray()
        ];
    }

    /**
     * Get engineers under a manager.
     */
    private function getEngineersByManager(int $managerId, int $projectId, bool $isStreetlight, array $filters): array
    {
        // Get engineers either by manager_id relationship OR those who have tasks under this manager
        $engineers = User::where('role', 1)
            ->where(function($query) use ($managerId, $projectId, $isStreetlight) {
                $query->where(function($q) use ($managerId, $projectId) {
                    $q->where('manager_id', $managerId)
                      ->where('project_id', $projectId);
                })
                ->orWhereHas($isStreetlight ? 'streetlightEngineerTasks' : 'engineerTasks', function($q) use ($managerId, $projectId) {
                    $q->where('manager_id', $managerId)
                      ->where('project_id', $projectId);
                });
            })
            ->distinct()
            ->get();

        return $engineers->map(function ($engineer) use ($projectId, $isStreetlight, $filters, $managerId) {
            $metrics = $this->calculateUserMetrics($engineer->id, $projectId, $isStreetlight, $filters, 1);
            
            // Get vendors under this engineer
            $vendors = User::where('role', 3)
                ->where(function($query) use ($engineer, $projectId, $isStreetlight) {
                    $query->where(function($q) use ($engineer, $projectId) {
                        $q->where('site_engineer_id', $engineer->id)
                          ->where('project_id', $projectId);
                    })
                    ->orWhereHas($isStreetlight ? 'streetlightVendorTasks' : 'vendorTasks', function($q) use ($engineer, $projectId) {
                        $q->where('engineer_id', $engineer->id)
                          ->where('project_id', $projectId);
                    });
                })
                ->distinct()
                ->get();

            return [
                'user' => $engineer,
                'metrics' => $metrics,
                'vendors' => $vendors->map(function ($vendor) use ($projectId, $isStreetlight, $filters) {
                    return [
                        'user' => $vendor,
                        'metrics' => $this->calculateUserMetrics($vendor->id, $projectId, $isStreetlight, $filters, 3),
                    ];
                })->sortByDesc('metrics.performance_percentage')->values()->toArray()
            ];
        })->sortByDesc('metrics.performance_percentage')->values()->toArray();
    }

    /**
     * Get vendors under a manager.
     */
    private function getVendorsByManager(int $managerId, int $projectId, bool $isStreetlight, array $filters): array
    {
        // Get vendors either through site_engineer relationship OR those who have tasks under this manager
        $vendors = User::where('role', 3)
            ->where(function($query) use ($managerId, $projectId, $isStreetlight) {
                $query->where(function($q) use ($managerId, $projectId) {
                    $q->whereHas('siteEngineer', function ($se) use ($managerId) {
                        $se->where('manager_id', $managerId);
                    })->where('project_id', $projectId);
                })
                ->orWhereHas($isStreetlight ? 'streetlightVendorTasks' : 'vendorTasks', function($q) use ($managerId, $projectId) {
                    $q->where('manager_id', $managerId)
                      ->where('project_id', $projectId);
                });
            })
            ->distinct()
            ->get();

        return $vendors->map(function ($vendor) use ($projectId, $isStreetlight, $filters) {
            return [
                'user' => $vendor,
                'metrics' => $this->calculateUserMetrics($vendor->id, $projectId, $isStreetlight, $filters, 3),
            ];
        })->sortByDesc('metrics.performance_percentage')->values()->toArray();
    }

    /**
     * Calculate performance metrics for a user.
     */
    private function calculateUserMetrics(int $userId, int $projectId, bool $isStreetlight, array $filters, int $userRole): array
    {
        $dateFilter = $this->buildDateFilter($filters);
        
        if ($isStreetlight) {
            return $this->calculateStreetlightMetrics($userId, $projectId, $dateFilter, $userRole);
        }
        
        return $this->calculateRooftopMetrics($userId, $projectId, $dateFilter, $userRole);
    }

    /**
     * Calculate metrics for streetlight projects.
     */
    private function calculateStreetlightMetrics(int $userId, int $projectId, array $dateFilter, int $userRole): array
    {
        $roleColumn = match($userRole) {
            1 => 'engineer_id',
            2 => 'manager_id',
            3 => 'vendor_id',
            default => 'engineer_id'
        };

        // Build query - fetch all tasks for this user in the project
        $query = StreetlightTask::where($roleColumn, $userId)
            ->where('project_id', $projectId);

        // Apply date filter only if enabled
        if (isset($dateFilter['enabled']) && $dateFilter['enabled']) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }

        $tasks = $query->with('site')->get();

        $taskIds = $tasks->pluck('id');
        
        // Total poles allocated
        $totalPoles = $tasks->sum(function ($task) {
            return optional($task->site)->total_poles ?? 0;
        });

        // Surveyed and installed poles
        $surveyedPoles = Pole::whereIn('task_id', $taskIds)->where('isSurveyDone', 1)->count();
        $installedPoles = Pole::whereIn('task_id', $taskIds)->where('isInstallationDone', 1)->count();

        // Completed tasks
        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', 'Pending')->count();

        // Backlogs
        $today = Carbon::today();
        $backlogTasks = $tasks->filter(function ($task) use ($today) {
            return $task->status === 'Pending' && Carbon::parse($task->end_date)->lt($today);
        })->count();

        $backlogPoles = $tasks->filter(function ($task) use ($today) {
            return Carbon::parse($task->end_date)->lt($today);
        })->sum(function ($task) {
            return optional($task->site)->total_poles ?? 0;
        });

        // Performance calculation
        $performancePercentage = $totalPoles > 0 
            ? round(($installedPoles / $totalPoles) * 100, 2) 
            : 0;

        return [
            'total_tasks' => $tasks->count(),
            'total_poles' => $totalPoles,
            'surveyed_poles' => $surveyedPoles,
            'installed_poles' => $installedPoles,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'backlog_tasks' => $backlogTasks,
            'backlog_poles' => $backlogPoles,
            'performance_percentage' => $performancePercentage,
            'survey_percentage' => $totalPoles > 0 ? round(($surveyedPoles / $totalPoles) * 100, 2) : 0,
            'install_percentage' => $totalPoles > 0 ? round(($installedPoles / $totalPoles) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate metrics for rooftop projects.
     */
    private function calculateRooftopMetrics(int $userId, int $projectId, array $dateFilter, int $userRole): array
    {
        $roleColumn = match($userRole) {
            1 => 'engineer_id',
            2 => 'manager_id',
            3 => 'vendor_id',
            default => 'engineer_id'
        };

        // Build query - fetch all tasks for this user in the project
        $query = Task::where($roleColumn, $userId)
            ->where('project_id', $projectId);

        // Apply date filter only if enabled
        if (isset($dateFilter['enabled']) && $dateFilter['enabled']) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }

        $tasks = $query->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', 'Pending')->count();
        $inProgressTasks = $tasks->where('status', 'In Progress')->count();

        // Backlogs
        $today = Carbon::today();
        $backlogTasks = $tasks->filter(function ($task) use ($today) {
            return $task->status === 'Pending' && Carbon::parse($task->end_date)->lt($today);
        })->count();

        // Performance calculation
        $performancePercentage = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 2) 
            : 0;

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'in_progress_tasks' => $inProgressTasks,
            'backlog_tasks' => $backlogTasks,
            'performance_percentage' => $performancePercentage,
            'completion_rate' => $performancePercentage,
        ];
    }

    /**
     * Get leaderboard for staff performance.
     */
    public function getLeaderboard(int $projectId, string $role, int $limit = 10, array $filters = []): array
    {
        $cacheKey = "performance.leaderboard.{$projectId}.{$role}." . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId, $role, $limit, $filters) {
            $project = Project::findOrFail($projectId);
            $isStreetlight = $project->project_type == 1;

            $roleId = match($role) {
                'manager' => 2,
                'engineer' => 1,
                'vendor' => 3,
                default => 1
            };

            $users = User::where('role', $roleId)
                ->where('project_id', $projectId)
                ->get();

            $leaderboard = $users->map(function ($user) use ($projectId, $isStreetlight, $filters, $roleId) {
                return [
                    'user' => $user,
                    'metrics' => $this->calculateUserMetrics($user->id, $projectId, $isStreetlight, $filters, $roleId),
                ];
            })->sortByDesc('metrics.performance_percentage')
              ->take($limit)
              ->values()
              ->toArray();

            return $leaderboard;
        });
    }

    /**
     * Get performance trends.
     */
    public function getPerformanceTrends(int $userId, int $projectId, string $period = 'daily', array $filters = []): array
    {
        $project = Project::findOrFail($projectId);
        $isStreetlight = $project->project_type == 1;
        $user = User::findOrFail($userId);

        $days = match($period) {
            'weekly' => 7,
            'monthly' => 30,
            default => 7
        };

        $trends = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateFilters = [
                'date_filter' => 'custom',
                'start_date' => $date->toDateString(),
                'end_date' => $date->toDateString(),
            ];

            $metrics = $this->calculateUserMetrics($userId, $projectId, $isStreetlight, $dateFilters, $user->role);
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'date_label' => $date->format('M d'),
                'performance' => $metrics['performance_percentage'],
                'completed' => $metrics['completed_tasks'] ?? 0,
                'pending' => $metrics['pending_tasks'] ?? 0,
            ];
        }

        return $trends;
    }

    /**
     * Get detailed user performance metrics.
     */
    public function getUserPerformanceMetrics(int $userId, int $projectId, array $filters = []): array
    {
        $user = User::findOrFail($userId);
        $project = Project::findOrFail($projectId);
        $isStreetlight = $project->project_type == 1;

        $metrics = $this->calculateUserMetrics($userId, $projectId, $isStreetlight, $filters, $user->role);
        
        return [
            'user' => $user,
            'project' => $project,
            'metrics' => $metrics,
            'trends' => $this->getPerformanceTrends($userId, $projectId, 'weekly', $filters),
        ];
    }

    /**
     * Get subordinate performance.
     */
    public function getSubordinatePerformance(int $managerId, int $projectId, string $subordinateType, array $filters = []): array
    {
        $project = Project::findOrFail($projectId);
        $isStreetlight = $project->project_type == 1;

        if ($subordinateType === 'engineers') {
            return $this->getEngineersByManager($managerId, $projectId, $isStreetlight, $filters);
        }

        return $this->getVendorsByManager($managerId, $projectId, $isStreetlight, $filters);
    }

    /**
     * Build date filter from request parameters.
     */
    private function buildDateFilter(array $filters): array
    {
        $dateFilter = $filters['date_filter'] ?? 'today';

        $start = Carbon::today();
        $end = Carbon::today()->endOfDay();

        switch ($dateFilter) {
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
            case 'all_time':
                return ['enabled' => false];
            case 'custom':
                if (isset($filters['start_date']) && isset($filters['end_date'])) {
                    $start = Carbon::parse($filters['start_date'])->startOfDay();
                    $end = Carbon::parse($filters['end_date'])->endOfDay();
                }
                break;
        }

        return [
            'enabled' => true,
            'start' => $start,
            'end' => $end,
        ];
    }
}

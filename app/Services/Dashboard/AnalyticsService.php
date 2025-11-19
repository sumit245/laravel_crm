<?php

namespace App\Services\Dashboard;

use App\Contracts\AnalyticsServiceInterface;
use App\Models\{Site, Streetlight, Task, StreetlightTask, Inventory, InventoryDispatch, Pole};
use App\Services\BaseService;
use Illuminate\Support\Facades\{Cache, DB};
use Carbon\Carbon;

/**
 * Analytics Service
 * 
 * Provides comprehensive analytics with intelligent caching
 */
class AnalyticsService extends BaseService implements AnalyticsServiceInterface
{
    /**
     * Calculate site statistics
     */
    public function calculateSiteStatistics(int $projectId): array
    {
        $cacheKey = "analytics:sites:{$projectId}:" . today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () use ($projectId) {
            $rooftopSites = Site::where('project_id', $projectId)->get();
            $streetlightSites = Streetlight::where('project_id', $projectId)->get();
            
            return [
                'rooftop' => [
                    'total' => $rooftopSites->count(),
                    'completed' => $rooftopSites->whereNotNull('completion_date')->count(),
                    'pending' => $rooftopSites->whereNull('completion_date')->count(),
                    'completion_percentage' => $rooftopSites->count() > 0 
                        ? round(($rooftopSites->whereNotNull('completion_date')->count() / $rooftopSites->count()) * 100, 2)
                        : 0,
                ],
                'streetlight' => [
                    'total' => $streetlightSites->count(),
                    'total_poles' => Pole::whereIn('site_id', $streetlightSites->pluck('id'))->count(),
                    'surveyed_poles' => Pole::whereIn('site_id', $streetlightSites->pluck('id'))->where('isSurveyDone', true)->count(),
                    'installed_poles' => Pole::whereIn('site_id', $streetlightSites->pluck('id'))->where('isInstallationDone', true)->count(),
                ],
                'by_district' => $streetlightSites->groupBy('district')->map->count()->toArray(),
            ];
        });
    }

    /**
     * Calculate inventory metrics
     */
    public function calculateInventoryMetrics(int $projectId): array
    {
        $cacheKey = "analytics:inventory:{$projectId}";
        
        return Cache::remember($cacheKey, 1800, function () use ($projectId) {
            $inventory = Inventory::where('project_id', $projectId)->get();
            $dispatches = InventoryDispatch::where('project_id', $projectId)->get();
            
            return [
                'total_items' => $inventory->count(),
                'total_value' => $inventory->sum(fn($i) => ($i->quantityStock ?? 0) * ($i->rate ?? 0)),
                'dispatched_value' => $dispatches->sum(fn($d) => ($d->quantity ?? 0) * ($d->rate ?? 0)),
                'in_store_value' => $inventory->sum(fn($i) => ($i->quantityStock ?? 0) * ($i->rate ?? 0)) - $dispatches->sum(fn($d) => ($d->quantity ?? 0) * ($d->rate ?? 0)),
                'low_stock_items' => $inventory->filter(fn($i) => ($i->quantityStock ?? 0) < 10)->count(),
                'dispatch_stats' => [
                    'total' => $dispatches->count(),
                    'consumed' => $dispatches->where('is_consumed', true)->count(),
                    'pending' => $dispatches->where('is_consumed', false)->count(),
                ],
            ];
        });
    }

    /**
     * Calculate task metrics
     */
    public function calculateTaskMetrics(int $projectId): array
    {
        $cacheKey = "analytics:tasks:{$projectId}:" . today()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 900, function () use ($projectId) {
            $rooftopTasks = Task::where('project_id', $projectId)->get();
            $streetlightTasks = StreetlightTask::where('project_id', $projectId)->get();
            
            $allTasks = $rooftopTasks->merge($streetlightTasks);
            
            return [
                'total' => $allTasks->count(),
                'by_status' => [
                    'pending' => $allTasks->where('status', 'Pending')->count(),
                    'in_progress' => $allTasks->where('status', 'In Progress')->count(),
                    'blocked' => $allTasks->where('status', 'Blocked')->count(),
                    'completed' => $allTasks->where('status', 'Completed')->count(),
                ],
                'completion_rate' => $allTasks->count() > 0 
                    ? round(($allTasks->where('status', 'Completed')->count() / $allTasks->count()) * 100, 2)
                    : 0,
                'overdue' => $allTasks->filter(fn($t) => 
                    $t->end_date && Carbon::parse($t->end_date)->isPast() && $t->status !== 'Completed'
                )->count(),
            ];
        });
    }

    /**
     * Calculate user performance
     */
    public function calculateUserPerformance(int $userId, string $period = 'month'): array
    {
        $cacheKey = "analytics:performance:{$userId}:{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $dateRange = $this->getDateRange($period);
            
            $tasks = Task::where('engineer_id', $userId)
                ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
                ->get();
            
            $streetlightTasks = StreetlightTask::where('engineer_id', $userId)
                ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
                ->get();
            
            $allTasks = $tasks->merge($streetlightTasks);
            $completedTasks = $allTasks->where('status', 'Completed');
            
            return [
                'period' => $period,
                'total_tasks' => $allTasks->count(),
                'completed_tasks' => $completedTasks->count(),
                'completion_rate' => $allTasks->count() > 0 
                    ? round(($completedTasks->count() / $allTasks->count()) * 100, 2)
                    : 0,
                'average_completion_time' => $this->calculateAverageCompletionTime($completedTasks),
                'efficiency_score' => $this->calculateEfficiencyScore($allTasks),
            ];
        });
    }

    /**
     * Generate trends
     */
    public function generateTrends(string $metric, string $period, int $projectId): array
    {
        $cacheKey = "analytics:trends:{$metric}:{$period}:{$projectId}";
        
        return Cache::remember($cacheKey, 21600, function () use ($metric, $period, $projectId) {
            $dateRange = $this->getTrendDateRange($period);
            $data = [];
            
            foreach ($dateRange as $date) {
                $value = match($metric) {
                    'tasks' => $this->getTaskCountForDate($projectId, $date),
                    'sites' => $this->getSiteCountForDate($projectId, $date),
                    'inventory' => $this->getInventoryValueForDate($projectId, $date),
                    default => 0,
                };
                
                $data[] = [
                    'date' => $date->format('Y-m-d'),
                    'value' => $value,
                ];
            }
            
            return $data;
        });
    }

    /**
     * Get date range for period
     */
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'today' => ['start' => today(), 'end' => now()],
            'week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'quarter' => ['start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()],
            'year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            default => ['start' => now()->subDays(30), 'end' => now()],
        };
    }

    /**
     * Get trend date range
     */
    protected function getTrendDateRange(string $period): array
    {
        $dates = [];
        $range = $this->getDateRange($period);
        
        $current = Carbon::parse($range['start']);
        $end = Carbon::parse($range['end']);
        
        while ($current->lte($end)) {
            $dates[] = $current->copy();
            $current->addDay();
        }
        
        return $dates;
    }

    /**
     * Calculate average completion time
     */
    protected function calculateAverageCompletionTime($tasks): float
    {
        if ($tasks->isEmpty()) return 0;
        
        $totalDays = 0;
        $count = 0;
        
        foreach ($tasks as $task) {
            if ($task->start_date && $task->updated_at) {
                $start = Carbon::parse($task->start_date);
                $end = Carbon::parse($task->updated_at);
                $totalDays += $start->diffInDays($end);
                $count++;
            }
        }
        
        return $count > 0 ? round($totalDays / $count, 2) : 0;
    }

    /**
     * Calculate efficiency score
     */
    protected function calculateEfficiencyScore($tasks): int
    {
        if ($tasks->isEmpty()) return 0;
        
        $completedOnTime = $tasks->filter(function($task) {
            return $task->status === 'Completed' && 
                   $task->end_date && 
                   $task->updated_at &&
                   Carbon::parse($task->updated_at)->lte(Carbon::parse($task->end_date));
        })->count();
        
        return (int)(($completedOnTime / $tasks->count()) * 100);
    }

    /**
     * Get task count for specific date
     */
    protected function getTaskCountForDate(int $projectId, Carbon $date): int
    {
        return Task::where('project_id', $projectId)
            ->whereDate('created_at', $date)
            ->count();
    }

    /**
     * Get site count for specific date
     */
    protected function getSiteCountForDate(int $projectId, Carbon $date): int
    {
        return Site::where('project_id', $projectId)
            ->whereDate('created_at', '<=', $date)
            ->count();
    }

    /**
     * Get inventory value for specific date
     */
    protected function getInventoryValueForDate(int $projectId, Carbon $date): float
    {
        return Inventory::where('project_id', $projectId)
            ->whereDate('created_at', '<=', $date)
            ->sum(DB::raw('quantityStock * rate')) ?? 0;
    }
}

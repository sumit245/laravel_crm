<?php

namespace App\Http\Controllers;

use App\Contracts\PerformanceServiceInterface;
use App\Enums\UserRole;
use App\Models\Project;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function __construct(
        protected PerformanceServiceInterface $performanceService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display the performance overview page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $projectId = $this->getSelectedProject($request, $user);
        $project = Project::findOrFail($projectId);

        // Build filters
        $filters = [
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Get hierarchical performance data
        $performanceData = $this->performanceService->getHierarchicalPerformance(
            $user->id,
            $user->role,
            $projectId,
            $filters
        );

        $leaderboard = [];
        if ($user->role == UserRole::ADMIN->value) {
            $leaderboard = $this->performanceService->getLeaderboard($projectId, 'manager', 10, $filters);
        } elseif ($user->role == UserRole::PROJECT_MANAGER->value) {
            $leaderboard = $this->performanceService->getLeaderboard($projectId, 'engineer', 10, $filters);
        }

        return view('performance.index', [
            'performanceData' => $performanceData,
            'leaderboard' => $leaderboard,
            'project' => $project,
            'projectId' => $projectId,
            'userRole' => $user->role,
            'isStreetlight' => $project->project_type == 1,
        ]);
    }

    /**
     * Get performance details for a specific user.
     */
    public function show(Request $request, int $userId)
    {
        $user = auth()->user();
        $projectId = $this->getSelectedProject($request, $user);

        // Build filters
        $filters = [
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $userPerformance = $this->performanceService->getUserPerformanceMetrics(
            $userId,
            $projectId,
            $filters
        );

        return view('performance.show', [
            'userPerformance' => $userPerformance,
            'projectId' => $projectId,
        ]);
    }

    /**
     * Get subordinate performance (AJAX endpoint).
     */
    public function subordinates(Request $request, int $managerId, string $type)
    {
        $projectId = $this->getSelectedProject($request, auth()->user());

        $filters = [
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $subordinates = $this->performanceService->getSubordinatePerformance(
            $managerId,
            $projectId,
            $type, // 'engineers' or 'vendors'
            $filters
        );

        return response()->json($subordinates);
    }

    /**
     * Get leaderboard data (AJAX endpoint).
     */
    public function leaderboard(Request $request, string $role)
    {
        $projectId = $this->getSelectedProject($request, auth()->user());

        $filters = [
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $limit = $request->query('limit', 10);

        $leaderboard = $this->performanceService->getLeaderboard(
            $projectId,
            $role,
            $limit,
            $filters
        );

        return response()->json($leaderboard);
    }

    /**
     * Get performance trends (AJAX endpoint).
     */
    public function trends(Request $request, int $userId)
    {
        $projectId = $this->getSelectedProject($request, auth()->user());
        $period = $request->query('period', 'daily'); // daily, weekly, monthly

        $filters = [
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $trends = $this->performanceService->getPerformanceTrends(
            $userId,
            $projectId,
            $period,
            $filters
        );

        return response()->json($trends);
    }

    /**
     * Get selected project ID.
     */
    private function getSelectedProject(Request $request, $user): int
    {
        if ($request->has('project_id')) {
            return (int) $request->project_id;
        }

        if ($user->project_id) {
            return (int) $user->project_id;
        }

        return Project::when($user->role !== UserRole::ADMIN->value, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->first()->id;
    }
}

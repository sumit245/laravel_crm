<?php

namespace App\Services\Dashboard;

use App\Enums\UserRole;
use App\Models\{
    Project, Site, Streetlight, Task, StreetlightTask, User, 
    Meet, DiscussionPoint, Tada, Conveyance, Journey, HotelExpense,
    Pole
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * Dashboard Analytics Service
 * 
 * Handles all analytics calculations for the redesigned dashboard
 */
class DashboardAnalyticsService
{
    /**
     * Get project performance analytics
     */
    public function getProjectPerformanceAnalytics(User $user, array $filters = []): array
    {
        $projectId = $filters['project_id'] ?? null;
        $dateRange = $this->getDateRange($filters);
        $isAdmin = $user->role === UserRole::ADMIN->value;

        // District-wise performance per PM
        $districtPerformance = $this->getDistrictWisePerformance($user, $projectId, $dateRange, $isAdmin);
        
        // Top performers per PM
        $topPerformers = $this->getTopPerformers($user, $projectId, $dateRange, $isAdmin);
        
        // Unified metrics (streetlight + rooftop)
        $unifiedMetrics = $this->getUnifiedMetrics($user, $projectId, $dateRange, $isAdmin);
        
        // Pole installation speed
        $poleSpeedMetrics = $this->getPoleInstallationSpeed($user, $projectId, $dateRange, $isAdmin);
        
        // Competitive leaderboard (for PMs)
        $leaderboard = $this->getCompetitiveLeaderboard($user, $projectId, $dateRange);

        return [
            'district_performance' => $districtPerformance,
            'top_performers' => $topPerformers,
            'unified_metrics' => $unifiedMetrics,
            'pole_speed_metrics' => $poleSpeedMetrics,
            'leaderboard' => $leaderboard,
        ];
    }

    /**
     * Get district-wise performance per project manager
     * Based on tasks assigned to PM (manager_id) in StreetlightTask
     */
    private function getDistrictWisePerformance(User $user, ?int $projectId, array $dateRange, bool $isAdmin): array
    {
        $query = User::where('role', UserRole::PROJECT_MANAGER->value);

        // If not admin, only show current user
        if (!$isAdmin) {
            $query->where('id', $user->id);
        }

        $projectManagers = $query->get();

        $result = [];
        foreach ($projectManagers as $pm) {
            try {
                // Get projects assigned to this PM
                $pmProjectsQuery = $pm->projects();
                if ($projectId) {
                    $pmProjectsQuery->where('projects.id', $projectId);
                }
                $pmProjects = $pmProjectsQuery->get();
                
                if ($pmProjects->isEmpty()) {
                    continue;
                }
            } catch (\Exception $e) {
                \Log::warning('Error getting projects for PM: ' . $pm->id, ['error' => $e->getMessage()]);
                continue;
            }

            // For each project, get districts from tasks assigned to this PM
            $districts = [];
            
            foreach ($pmProjects as $project) {
                if ($project->project_type == 1) { // Streetlight
                    // Get unique districts from tasks assigned to this PM
                    $pmTasks = StreetlightTask::where('manager_id', $pm->id)
                        ->where('project_id', $project->id)
                        ->with('site')
                        ->get();

                    // Group by district
                    $districtGroups = [];
                    foreach ($pmTasks as $task) {
                        if ($task->site && $task->site->district) {
                            $districtName = $task->site->district;
                            if (!isset($districtGroups[$districtName])) {
                                $districtGroups[$districtName] = [];
                            }
                            $districtGroups[$districtName][] = $task->id;
                        }
                    }

                    // Calculate metrics for each district
                    foreach ($districtGroups as $districtName => $taskIds) {
                        // Get site IDs from tasks
                        $siteIds = StreetlightTask::whereIn('id', $taskIds)
                            ->pluck('site_id')
                            ->unique()
                            ->toArray();

                        // Total poles assigned to PM in this district (from Streetlight sites)
                        $totalPoles = Streetlight::whereIn('id', $siteIds)
                            ->where('district', $districtName)
                            ->sum('total_poles');

                        // Surveyed poles by PM's team (from poles table)
                        $surveyedPoles = Pole::whereIn('task_id', $taskIds)
                            ->where('isSurveyDone', true)
                            ->whereBetween('updated_at', $dateRange)
                            ->count();

                        // Installed poles by PM's team (from poles table)
                        $installedPoles = Pole::whereIn('task_id', $taskIds)
                            ->where('isInstallationDone', true)
                            ->whereBetween('updated_at', $dateRange)
                            ->count();

                        // Calculate progress percentages
                        $surveyedProgress = $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0;
                        $installedProgress = $totalPoles > 0 ? ($installedPoles / $totalPoles) * 100 : 0;
                        $billableProgress = $installedProgress; // Same as installed for now

                        // Use installed progress as overall progress
                        $overallProgress = $installedProgress;

                        // Store district info (only one district per PM typically)
                        if (!isset($districts[$districtName])) {
                            $districts[$districtName] = [
                                'name' => $districtName,
                                'total_poles' => 0,
                                'surveyed_poles' => 0,
                                'installed_poles' => 0,
                                'surveyed_progress' => 0,
                                'installed_progress' => 0,
                                'billable_progress' => 0,
                                'progress' => 0,
                            ];
                        }

                        // Aggregate if same district appears in multiple projects
                        $districts[$districtName]['total_poles'] += $totalPoles;
                        $districts[$districtName]['surveyed_poles'] += $surveyedPoles;
                        $districts[$districtName]['installed_poles'] += $installedPoles;
                    }
                }
            }

            // Recalculate aggregated progress for each district
            foreach ($districts as $districtName => &$district) {
                $district['surveyed_progress'] = $district['total_poles'] > 0 
                    ? ($district['surveyed_poles'] / $district['total_poles']) * 100 
                    : 0;
                $district['installed_progress'] = $district['total_poles'] > 0 
                    ? ($district['installed_poles'] / $district['total_poles']) * 100 
                    : 0;
                $district['billable_progress'] = $district['installed_progress'];
                $district['progress'] = $district['installed_progress'];
            }

            // Calculate overall metrics across all districts
            $totalPoles = array_sum(array_column($districts, 'total_poles'));
            $totalSurveyed = array_sum(array_column($districts, 'surveyed_poles'));
            $totalInstalled = array_sum(array_column($districts, 'installed_poles'));

            // Calculate overall progress from total poles (not average of district progress)
            $overallProgress = $totalPoles > 0 
                ? ($totalInstalled / $totalPoles) * 100 
                : 0;
            
            $surveyedProgress = $totalPoles > 0 
                ? ($totalSurveyed / $totalPoles) * 100 
                : 0;
            
            $installedProgress = $overallProgress;

            // Get primary district (the one with most poles, or first one)
            $primaryDistrict = null;
            if (count($districts) > 0) {
                $primaryDistrict = collect($districts)->sortByDesc('total_poles')->first();
            }

            // Each PM typically has 1 district, but we show all districts they're assigned to
            $result[] = [
                'pm_id' => $pm->id,
                'pm_name' => $pm->name ?? ($pm->firstName . ' ' . $pm->lastName) ?? 'Unknown PM',
                'districts' => array_values($districts), // Keep all districts for reference
                'primary_district' => $primaryDistrict ? $primaryDistrict['name'] : null,
                'district_count' => count($districts),
                'overall_progress' => round($overallProgress, 2),
                'total_poles' => $totalPoles,
                'surveyed_poles' => $totalSurveyed,
                'installed_poles' => $totalInstalled,
                'surveyed_progress' => round($surveyedProgress, 2),
                'installed_progress' => round($installedProgress, 2),
                'billable_progress' => round($installedProgress, 2),
            ];
        }

        return $result;
    }

    /**
     * Get top performing engineers and vendors per PM
     */
    private function getTopPerformers(User $user, ?int $projectId, array $dateRange, bool $isAdmin): array
    {
        $pmId = $isAdmin ? null : $user->id;

        // Get engineers - filter by project assignment or tasks if project is selected
        $engineersQuery = User::where('role', UserRole::SITE_ENGINEER->value)
            ->when($pmId, fn($q) => $q->where('manager_id', $pmId))
            ->when($projectId, function ($q) use ($projectId) {
                // Only get engineers who are either:
                // 1. Assigned to this project (via pivot table), OR
                // 2. Have tasks in this project
                $q->where(function ($query) use ($projectId) {
                    $query->whereHas('projects', function ($sq) use ($projectId) {
                        $sq->where('projects.id', $projectId);
                    })->orWhereHas('streetlightEngineerTasks', function ($sq) use ($projectId) {
                        $sq->where('project_id', $projectId);
                    })->orWhereHas('engineerTasks', function ($sq) use ($projectId) {
                        $sq->where('project_id', $projectId);
                    });
                });
            });

        $engineers = $engineersQuery->get()->map(function ($engineer) use ($projectId, $dateRange) {
            // Get tasks for this engineer in the selected project
            $tasks = StreetlightTask::where('engineer_id', $engineer->id)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->whereBetween('updated_at', $dateRange)
                ->get();

            $completed = $tasks->where('status', 'Completed')->count();
            $total = $tasks->count();
            $progress = $total > 0 ? ($completed / $total) * 100 : 0;

            // For streetlight: count poles in the selected project
            $poles = Pole::whereHas('task', function ($q) use ($engineer, $projectId) {
                $q->where('engineer_id', $engineer->id)
                    ->when($projectId, fn($q2) => $q2->where('project_id', $projectId));
            })->where('isInstallationDone', true)
            ->whereBetween('updated_at', $dateRange)
            ->count();

            // For rooftop: count sites in the selected project
            $sites = Site::where('site_engineer', $engineer->id)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->whereNotNull('commissioning_date')
                ->whereBetween('commissioning_date', $dateRange)
                ->count();

            return [
                'id' => $engineer->id,
                'name' => $engineer->name ?? ($engineer->firstName . ' ' . $engineer->lastName) ?? 'Unknown Engineer',
                'sites' => $sites,
                'poles' => $poles,
                'progress' => round($progress, 2),
            ];
        })
        ->filter(function ($engineer) use ($projectId) {
            // Only include engineers who have work in the selected project
            if ($projectId) {
                return $engineer['poles'] > 0 || $engineer['sites'] > 0 || $engineer['progress'] > 0;
            }
            return true;
        })
        ->sortByDesc('progress')->take(10)->values();

        // Get vendors - filter by project assignment or tasks if project is selected
        $vendorsQuery = User::where('role', UserRole::VENDOR->value)
            ->when($pmId, fn($q) => $q->whereHas('siteEngineer', function ($sq) use ($pmId) {
                $sq->where('manager_id', $pmId);
            }))
            ->when($projectId, function ($q) use ($projectId) {
                // Only get vendors who are either:
                // 1. Assigned to this project (via pivot table), OR
                // 2. Have tasks in this project
                $q->where(function ($query) use ($projectId) {
                    $query->whereHas('projects', function ($sq) use ($projectId) {
                        $sq->where('projects.id', $projectId);
                    })->orWhereHas('streetlightVendorTasks', function ($sq) use ($projectId) {
                        $sq->where('project_id', $projectId);
                    })->orWhereHas('vendorTasks', function ($sq) use ($projectId) {
                        $sq->where('project_id', $projectId);
                    });
                });
            });

        $vendors = $vendorsQuery->get()->map(function ($vendor) use ($projectId, $dateRange) {
            // Get tasks for this vendor in the selected project
            $tasks = StreetlightTask::where('vendor_id', $vendor->id)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->whereBetween('updated_at', $dateRange)
                ->get();

            $completed = $tasks->where('status', 'Completed')->count();
            $total = $tasks->count();
            $progress = $total > 0 ? ($completed / $total) * 100 : 0;

            // Count poles in the selected project
            $poles = Pole::whereHas('task', function ($q) use ($vendor, $projectId) {
                $q->where('vendor_id', $vendor->id)
                    ->when($projectId, fn($q2) => $q2->where('project_id', $projectId));
            })->where('isInstallationDone', true)
            ->whereBetween('updated_at', $dateRange)
            ->count();

            return [
                'id' => $vendor->id,
                'name' => $vendor->name ?? ($vendor->firstName . ' ' . $vendor->lastName) ?? 'Unknown Vendor',
                'poles' => $poles,
                'progress' => round($progress, 2),
            ];
        })
        ->filter(function ($vendor) use ($projectId) {
            // Only include vendors who have work in the selected project
            if ($projectId) {
                return $vendor['poles'] > 0 || $vendor['progress'] > 0;
            }
            return true;
        })
        ->sortByDesc('progress')->take(10)->values();

        return [
            'engineers' => $engineers,
            'vendors' => $vendors,
        ];
    }

    /**
     * Get unified metrics for streetlight and rooftop projects
     */
    private function getUnifiedMetrics(User $user, ?int $projectId, array $dateRange, bool $isAdmin): array
    {
        $projectQuery = Project::query()
            ->when($projectId, fn($q) => $q->where('id', $projectId))
            ->when(!$isAdmin, function ($q) use ($user) {
                $q->whereHas('users', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            });

        // Streetlight metrics
        $streetlightProjects = (clone $projectQuery)->where('project_type', 1)->get();
        $streetlightMetrics = [
            'total_poles' => 0,
            'surveyed_poles' => 0,
            'installed_poles' => 0,
            'progress' => 0,
        ];

        foreach ($streetlightProjects as $project) {
            $streetlightMetrics['total_poles'] += Streetlight::where('project_id', $project->id)
                ->sum('total_poles');
            
            $streetlightMetrics['surveyed_poles'] += Pole::whereHas('task', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })->where('isSurveyDone', true)
            ->whereBetween('updated_at', $dateRange)
            ->count();

            $streetlightMetrics['installed_poles'] += Pole::whereHas('task', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })->where('isInstallationDone', true)
            ->whereBetween('updated_at', $dateRange)
            ->count();
        }

        if ($streetlightMetrics['total_poles'] > 0) {
            $streetlightMetrics['progress'] = ($streetlightMetrics['installed_poles'] / $streetlightMetrics['total_poles']) * 100;
        }

        // Rooftop metrics
        $rooftopProjects = (clone $projectQuery)->where('project_type', 0)->get();
        $rooftopMetrics = [
            'total_sites' => 0,
            'completed_sites' => 0,
            'in_progress_sites' => 0,
            'progress' => 0,
        ];

        foreach ($rooftopProjects as $project) {
            $rooftopMetrics['total_sites'] += Site::where('project_id', $project->id)->count();
            $rooftopMetrics['completed_sites'] += Site::where('project_id', $project->id)
                ->whereNotNull('commissioning_date')
                ->whereBetween('commissioning_date', $dateRange)
                ->count();
            $rooftopMetrics['in_progress_sites'] += Site::where('project_id', $project->id)
                ->whereNull('commissioning_date')
                ->count();
        }

        if ($rooftopMetrics['total_sites'] > 0) {
            $rooftopMetrics['progress'] = ($rooftopMetrics['completed_sites'] / $rooftopMetrics['total_sites']) * 100;
        }

        // Combined metrics
        $combined = [
            'total' => $streetlightMetrics['total_poles'] + $rooftopMetrics['total_sites'],
            'completed' => $streetlightMetrics['installed_poles'] + $rooftopMetrics['completed_sites'],
            'progress' => 0,
        ];

        if ($combined['total'] > 0) {
            $combined['progress'] = ($combined['completed'] / $combined['total']) * 100;
        }

        return [
            'streetlight' => $streetlightMetrics,
            'rooftop' => $rooftopMetrics,
            'combined' => $combined,
        ];
    }

    /**
     * Get pole installation speed metrics by panchayat
     */
    private function getPoleInstallationSpeed(User $user, ?int $projectId, array $dateRange, bool $isAdmin): array
    {
        $query = Streetlight::query()
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when(!$isAdmin, function ($q) use ($user) {
                $q->whereHas('project.users', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            })
            ->select('panchayat', 'district', DB::raw('SUM(total_poles) as total_poles'))
            ->groupBy('panchayat', 'district');

        $panchayats = $query->get();

        $result = [];
        foreach ($panchayats as $panchayat) {
            $installed = Pole::whereHas('task', function ($q) use ($panchayat, $projectId) {
                $q->when($projectId, fn($q2) => $q2->where('project_id', $projectId))
                    ->whereHas('site', function ($sq) use ($panchayat) {
                        $sq->where('panchayat', $panchayat->panchayat)
                            ->where('district', $panchayat->district);
                    });
            })->where('isInstallationDone', true)
            ->whereBetween('updated_at', $dateRange)
            ->count();

            $progress = $panchayat->total_poles > 0 
                ? ($installed / $panchayat->total_poles) * 100 
                : 0;

            // Calculate speed (poles per day in date range)
            $daysDiff = max(1, Carbon::parse($dateRange[0])->diffInDays(Carbon::parse($dateRange[1])));
            $speed = $daysDiff > 0 ? $installed / $daysDiff : 0;

            // Determine speed status
            $speedStatus = 'slow';
            if ($speed >= 5) {
                $speedStatus = 'fast';
            } elseif ($speed >= 2) {
                $speedStatus = 'medium';
            }

            // Calculate trend (compare with previous period)
            $previousDateRange = [
                Carbon::parse($dateRange[0])->subDays($daysDiff)->toDateTimeString(),
                Carbon::parse($dateRange[0])->toDateTimeString(),
            ];
            
            $previousInstalled = Pole::whereHas('task', function ($q) use ($panchayat, $projectId) {
                $q->when($projectId, fn($q2) => $q2->where('project_id', $projectId))
                    ->whereHas('site', function ($sq) use ($panchayat) {
                        $sq->where('panchayat', $panchayat->panchayat)
                            ->where('district', $panchayat->district);
                    });
            })->where('isInstallationDone', true)
            ->whereBetween('updated_at', $previousDateRange)
            ->count();

            $previousSpeed = $daysDiff > 0 ? $previousInstalled / $daysDiff : 0;
            $trend = $speed > $previousSpeed ? 'up' : ($speed < $previousSpeed ? 'down' : 'stable');
            $trendPercent = $previousSpeed > 0 ? abs((($speed - $previousSpeed) / $previousSpeed) * 100) : 0;

            $result[] = [
                'panchayat' => $panchayat->panchayat,
                'district' => $panchayat->district,
                'total_poles' => $panchayat->total_poles,
                'installed_poles' => $installed,
                'speed' => round($speed, 2),
                'speed_status' => $speedStatus,
                'progress' => round($progress, 2),
                'trend' => $trend,
                'trend_percent' => round($trendPercent, 2),
            ];
        }

        // Sort by speed (fastest first)
        usort($result, fn($a, $b) => $b['speed'] <=> $a['speed']);

        return $result;
    }

    /**
     * Get competitive leaderboard for project managers
     */
    private function getCompetitiveLeaderboard(User $user, ?int $projectId, array $dateRange): array
    {
        // Get district performance data which already has installed poles calculated
        $districtPerformance = $this->getDistrictWisePerformance($user, $projectId, $dateRange, $user->role === UserRole::ADMIN->value);

        $leaderboard = [];
        foreach ($districtPerformance as $pm) {
            $leaderboard[] = [
                'pm_id' => $pm['pm_id'],
                'pm_name' => $pm['pm_name'],
                'installed_poles' => $pm['installed_poles'] ?? 0,
                'total_poles' => $pm['total_poles'] ?? 0,
                'progress' => $pm['installed_progress'] ?? $pm['overall_progress'] ?? 0,
            ];
        }

        // Sort by progress percentage descending (primary), then by installed_poles (secondary)
        usort($leaderboard, function($a, $b) {
            // First sort by progress percentage (highest % first)
            if (abs($b['progress'] - $a['progress']) > 0.01) { // Use small threshold for float comparison
                return $b['progress'] <=> $a['progress'];
            }
            // If progress is equal (or very close), sort by installed poles
            return $b['installed_poles'] <=> $a['installed_poles'];
        });

        // Add rank
        foreach ($leaderboard as $index => &$entry) {
            $entry['rank'] = $index + 1;
            
            // Calculate trend (simplified - would need historical data for accurate trend)
            $entry['trend'] = 'stable';
            $entry['trend_percent'] = 0;
        }

        return $leaderboard;
    }

    /**
     * Get meeting summary analytics
     */
    public function getMeetingAnalytics(User $user, array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        $projectId = $filters['project_id'] ?? null;
        $isAdmin = $user->role === UserRole::ADMIN->value;

        // Get meetings based on role
        $meetingsQuery = Meet::query()
            ->when(!$isAdmin, function ($q) use ($user) {
                $q->whereHas('participants', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            })
            ->whereBetween('meet_date', [
                Carbon::parse($dateRange[0])->toDateString(),
                Carbon::parse($dateRange[1])->toDateString(),
            ]);

        $meetings = $meetingsQuery->get();

        // Total meetings
        $totalMeetings = $meetings->count();

        // Active discussions (meetings with discussion points)
        $activeDiscussions = $meetings->filter(function ($meeting) {
            return $meeting->discussionPoints()->count() > 0;
        })->count();

        // Discussions this month
        $thisMonthStart = now()->startOfMonth();
        $discussionsThisMonth = DiscussionPoint::whereHas('meet', function ($q) use ($user, $isAdmin) {
            if (!$isAdmin) {
                $q->whereHas('participants', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            }
        })->where('created_at', '>=', $thisMonthStart)->count();

        // Meeting breakdown by type
        $meetingTypes = $meetings->groupBy('type')->map(function ($typeMeetings, $type) {
            $totalDuration = 0; // Would need duration field or calculate from meet_time
            $count = $typeMeetings->count();
            
            return [
                'type' => $type ?? 'Other',
                'count' => $count,
                'avg_duration' => 0, // Placeholder - would need actual duration data
                'trend' => 'stable', // Placeholder
            ];
        })->values();

        // Recent meetings (user-specific)
        $recentMeetings = $meetings->sortByDesc('meet_date')->take(10)->map(function ($meeting) use ($user) {
            $participants = $meeting->participants;
            $userParticipant = $participants->contains('id', $user->id);
            $otherCount = $participants->count() - ($userParticipant ? 1 : 0);

            return [
                'id' => $meeting->id,
                'date' => $meeting->meet_date->format('Y-m-d'),
                'title' => $meeting->title,
                'participants_count' => $participants->count(),
                'you_participated' => $userParticipant,
                'other_count' => $otherCount,
            ];
        })->values();

        // Discussion points summary
        $discussionPointsQuery = DiscussionPoint::whereHas('meet', function ($q) use ($user, $isAdmin, $dateRange) {
            if (!$isAdmin) {
                $q->whereHas('participants', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            }
        })->whereBetween('created_at', $dateRange);

        $totalPoints = $discussionPointsQuery->count();
        $resolvedPoints = (clone $discussionPointsQuery)->where('status', 'Resolved')->count();
        $pendingPoints = (clone $discussionPointsQuery)->where('status', 'Pending')->count();

        // Top discussion topics
        $topTopics = DiscussionPoint::whereHas('meet', function ($q) use ($user, $isAdmin) {
            if (!$isAdmin) {
                $q->whereHas('participants', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            }
        })
        ->select('title', DB::raw('COUNT(*) as count'))
        ->groupBy('title')
        ->orderByDesc('count')
        ->limit(10)
        ->get()
        ->map(function ($topic) use ($user, $isAdmin) {
            $resolved = DiscussionPoint::where('title', $topic->title)
                ->whereHas('meet', function ($q) use ($user, $isAdmin) {
                    if (!$isAdmin) {
                        $q->whereHas('participants', function ($sq) use ($user) {
                            $sq->where('users.id', $user->id);
                        });
                    }
                })
                ->where('status', 'Resolved')
                ->count();
            
            $resolutionRate = $topic->count > 0 ? ($resolved / $topic->count) * 100 : 0;

            return [
                'topic' => $topic->title,
                'count' => $topic->count,
                'resolution_rate' => round($resolutionRate, 2),
            ];
        });

        return [
            'overview' => [
                'total_meetings' => $totalMeetings,
                'active_discussions' => $activeDiscussions,
                'discussions_this_month' => $discussionsThisMonth,
            ],
            'meeting_types' => $meetingTypes,
            'recent_meetings' => $recentMeetings,
            'discussion_points' => [
                'total' => $totalPoints,
                'resolved' => $resolvedPoints,
                'pending' => $pendingPoints,
            ],
            'top_topics' => $topTopics,
        ];
    }

    /**
     * Get TA/DA bills analytics
     */
    public function getTadaAnalytics(User $user, array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        $projectId = $filters['project_id'] ?? null;
        $isAdmin = $user->role === UserRole::ADMIN->value;

        $tadaQuery = Tada::query()
            ->when(!$isAdmin, function ($q) use ($user) {
                // PM can see their team's bills
                if ($user->role === UserRole::PROJECT_MANAGER->value) {
                    $q->whereHas('user', function ($sq) use ($user) {
                        $sq->where('manager_id', $user->id)
                            ->orWhere('id', $user->id);
                    });
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->whereBetween('date_of_departure', [
                Carbon::parse($dateRange[0])->toDateString(),
                Carbon::parse($dateRange[1])->toDateString(),
            ]);

        $tadas = $tadaQuery->get();

        // Financial overview
        $totalAmount = $tadas->sum('amount');
        $thisMonth = now()->startOfMonth();
        $disbursedThisMonth = (clone $tadaQuery)
            ->where('status', 'Approved')
            ->where('date_of_departure', '>=', $thisMonth)
            ->sum('amount');
        
        $pendingAmount = (clone $tadaQuery)
            ->where('status', 'Pending')
            ->sum('amount');

        // Distance travelled (from conveyances)
        $conveyancesForDistance = Conveyance::whereHas('user', function ($q) use ($tadas) {
            $q->whereIn('id', $tadas->pluck('user_id'));
        })
        ->whereBetween('created_at', $dateRange)
        ->get();

        $totalDistance = $conveyancesForDistance->sum('kilometer') ?? 0;
        $avgPerTravel = $tadas->count() > 0 ? $totalAmount / $tadas->count() : 0;
        $avgPerKm = $totalDistance > 0 ? $totalAmount / $totalDistance : 0;

        // Highest traveller
        $topTraveller = $tadas->groupBy('user_id')
            ->map(function ($userTadas, $userId) {
                return [
                    'user_id' => $userId,
                    'count' => $userTadas->count(),
                    'amount' => $userTadas->sum('amount'),
                ];
            })
            ->sortByDesc('count')
            ->first();

        $topTravellerUser = $topTraveller ? User::find($topTraveller['user_id']) : null;

        // Per-project disbursals
        $perProjectDisbursals = [];
        // Note: Tada model doesn't have project_id, would need to link via user's projects
        // This is a simplified version
        $userProjects = [];
        foreach ($tadas as $tada) {
            $tadaUser = $tada->user;
            $userProjectsList = $tadaUser->projects;
            
            foreach ($userProjectsList as $project) {
                if (!isset($perProjectDisbursals[$project->id])) {
                    $perProjectDisbursals[$project->id] = [
                        'project_id' => $project->id,
                        'project_name' => $project->project_name,
                        'amount' => 0,
                        'travels' => 0,
                    ];
                }
                $perProjectDisbursals[$project->id]['amount'] += $tada->amount;
                $perProjectDisbursals[$project->id]['travels']++;
            }
        }

        // Top travellers
        $topTravellers = $tadas->groupBy('user_id')
            ->map(function ($userTadas, $userId) use ($dateRange) {
                $tadaUser = User::find($userId);
                $distance = Conveyance::where('user_id', $userId)
                    ->whereBetween('created_at', $dateRange)
                    ->sum('kilometer') ?? 0;

                return [
                    'user_id' => $userId,
                    'name' => $tadaUser ? ($tadaUser->name ?? ($tadaUser->firstName . ' ' . $tadaUser->lastName) ?? 'Unknown') : 'Unknown',
                    'travels' => $userTadas->count(),
                    'distance' => $distance,
                    'amount' => $userTadas->sum('amount'),
                ];
            })
            ->sortByDesc('travels')
            ->take(10)
            ->values();

        // Travel breakdown
        $conveyances = Conveyance::whereHas('user', function ($q) use ($tadas) {
            $q->whereIn('id', $tadas->pluck('user_id'));
        })
        ->whereBetween('created_at', $dateRange)
        ->get();

        $byVehicle = $conveyances->groupBy('vehicle_category')->map(function ($vehicleConveyances, $category) {
            return [
                'category' => $category,
                'count' => $vehicleConveyances->count(),
                'percentage' => 0, // Will calculate after
            ];
        });

        $totalConveyances = $conveyances->count();
        $byVehicle = $byVehicle->map(function ($item) use ($totalConveyances) {
            $item['percentage'] = $totalConveyances > 0 ? ($item['count'] / $totalConveyances) * 100 : 0;
            return $item;
        });

        $byStatus = $tadas->groupBy('status')->map(function ($statusTadas, $status) use ($tadas) {
            return [
                'status' => $status,
                'count' => $statusTadas->count(),
                'percentage' => $tadas->count() > 0 ? ($statusTadas->count() / $tadas->count()) * 100 : 0,
            ];
        });

        return [
            'financial_overview' => [
                'total_amount' => $totalAmount,
                'disbursed_this_month' => $disbursedThisMonth,
                'pending_amount' => $pendingAmount,
                'distance_travelled' => $totalDistance,
                'avg_per_travel' => round($avgPerTravel, 2),
                'avg_per_km' => round($avgPerKm, 2),
                'highest_traveller' => $topTravellerUser ? [
                    'id' => $topTravellerUser->id,
                    'name' => $topTravellerUser->name ?? ($topTravellerUser->firstName . ' ' . $topTravellerUser->lastName) ?? 'Unknown',
                    'travels' => $topTraveller['count'],
                ] : null,
            ],
            'per_project_disbursals' => array_values($perProjectDisbursals),
            'top_travellers' => $topTravellers,
            'travel_breakdown' => [
                'by_vehicle' => $byVehicle->values(),
                'by_status' => $byStatus->values(),
            ],
        ];
    }

    /**
     * Get date range from filters
     */
    private function getDateRange(array $filters): array
    {
        $filter = $filters['date_filter'] ?? 'this_month';
        
        switch ($filter) {
            case 'today':
                return [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()];
            case 'this_week':
                return [now()->startOfWeek()->toDateTimeString(), now()->endOfWeek()->toDateTimeString()];
            case 'this_month':
                return [now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString()];
            case 'all_time':
                return [Carbon::createFromTimestamp(0)->toDateTimeString(), now()->toDateTimeString()];
            case 'custom':
                $start = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
                $end = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
                return [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ];
            default:
                return [now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString()];
        }
    }
}


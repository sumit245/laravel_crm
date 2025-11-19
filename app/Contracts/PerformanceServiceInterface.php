<?php

namespace App\Contracts;

interface PerformanceServiceInterface
{
    /**
     * Get hierarchical performance data based on user role.
     *
     * @param int $userId Current user ID
     * @param int $userRole Current user role (0=Admin, 1=Engineer, 2=Manager, 3=Vendor)
     * @param int $projectId Project ID to filter by
     * @param array $filters Date filters and other parameters
     * @return array Hierarchical performance data
     */
    public function getHierarchicalPerformance(int $userId, int $userRole, int $projectId, array $filters = []): array;

    /**
     * Get staff leaderboard (top performers).
     *
     * @param int $projectId
     * @param string $role Role type (manager, engineer, vendor)
     * @param int $limit Number of top performers to return
     * @param array $filters Date filters
     * @return array
     */
    public function getLeaderboard(int $projectId, string $role, int $limit = 10, array $filters = []): array;

    /**
     * Get performance trends for a specific user.
     *
     * @param int $userId
     * @param int $projectId
     * @param string $period 'daily', 'weekly', 'monthly'
     * @param array $filters
     * @return array
     */
    public function getPerformanceTrends(int $userId, int $projectId, string $period = 'daily', array $filters = []): array;

    /**
     * Get detailed performance metrics for a user.
     *
     * @param int $userId
     * @param int $projectId
     * @param array $filters
     * @return array
     */
    public function getUserPerformanceMetrics(int $userId, int $projectId, array $filters = []): array;

    /**
     * Get subordinate performance for managers.
     *
     * @param int $managerId
     * @param int $projectId
     * @param string $subordinateType 'engineers' or 'vendors'
     * @param array $filters
     * @return array
     */
    public function getSubordinatePerformance(int $managerId, int $projectId, string $subordinateType, array $filters = []): array;
}

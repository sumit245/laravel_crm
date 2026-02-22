<?php

namespace App\Contracts;

/**
 * Contract for analytics services. Defines methods for computing dashboard metrics, district-wise
 * breakdowns, top performers, and aggregated statistics.
 *
 * Data Flow:
 *   HomeController → AnalyticsService (implements this) → Aggregated DB queries →
 *   Returns analytics data
 *
 * @business-domain Dashboard & Reporting
 * @package App\Contracts
 */
interface AnalyticsServiceInterface extends ServiceInterface
{
    /**
     * Calculate site statistics.
     *
     * @param  int  $projectId  The project identifier
     * @return array;  
     */
    public function calculateSiteStatistics(int $projectId): array;
    /**
     * Calculate inventory metrics.
     *
     * @param  int  $projectId  The project identifier
     * @return array;  
     */
    public function calculateInventoryMetrics(int $projectId): array;
    /**
     * Calculate task metrics.
     *
     * @param  int  $projectId  The project identifier
     * @return array;  
     */
    public function calculateTaskMetrics(int $projectId): array;
    /**
     * Calculate user performance.
     *
     * @param  int  $userId  The user identifier
     * @param  string  $period  
     * @return array;  
     */
    public function calculateUserPerformance(int $userId, string $period = 'month'): array;
    /**
     * Generate trends.
     *
     * @param  string  $metric  
     * @param  string  $period  
     * @param  int  $projectId  The project identifier
     * @return array;  
     */
    public function generateTrends(string $metric, string $period, int $projectId): array;
}

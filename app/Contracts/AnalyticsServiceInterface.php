<?php

namespace App\Contracts;

/**
 * Analytics Service Interface
 */
interface AnalyticsServiceInterface extends ServiceInterface
{
    public function calculateSiteStatistics(int $projectId): array;
    public function calculateInventoryMetrics(int $projectId): array;
    public function calculateTaskMetrics(int $projectId): array;
    public function calculateUserPerformance(int $userId, string $period = 'month'): array;
    public function generateTrends(string $metric, string $period, int $projectId): array;
}

<?php

namespace App\Contracts;

/**
 * Dashboard Service Interface
 */
interface DashboardServiceInterface extends ServiceInterface
{
    public function getDashboardData(int $userId, string $userRole, array $filters = []): array;
    public function getAdminDashboard(array $filters = []): array;
    public function getProjectManagerDashboard(int $userId, array $filters = []): array;
    public function getSiteEngineerDashboard(int $userId, array $filters = []): array;
    public function getStoreInchargeDashboard(int $userId, array $filters = []): array;
    public function getVendorDashboard(int $userId, array $filters = []): array;
}

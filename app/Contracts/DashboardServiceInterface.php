<?php

namespace App\Contracts;

/**
 * Contract for dashboard data services. Defines methods for fetching and structuring dashboard
 * widgets, KPIs, and summary data.
 *
 * Data Flow:
 *   HomeController → DashboardService (implements this) → Structured widget data →
 *   Dashboard view
 *
 * @business-domain Dashboard & Reporting
 * @package App\Contracts
 */
interface DashboardServiceInterface extends ServiceInterface
{
    /**
     * Get the dashboard data.
     *
     * @param  int  $userId  The user identifier
     * @param  string  $userRole  
     * @param  array  $filters  
     * @return array;  
     */
    public function getDashboardData(int $userId, string $userRole, array $filters = []): array;
    /**
     * Get the admin dashboard.
     *
     * @param  array  $filters  
     * @return array;  
     */
    public function getAdminDashboard(array $filters = []): array;
    /**
     * Get the project manager dashboard.
     *
     * @param  int  $userId  The user identifier
     * @param  array  $filters  
     * @return array;  
     */
    public function getProjectManagerDashboard(int $userId, array $filters = []): array;
    /**
     * Get the site engineer dashboard.
     *
     * @param  int  $userId  The user identifier
     * @param  array  $filters  
     * @return array;  
     */
    public function getSiteEngineerDashboard(int $userId, array $filters = []): array;
    /**
     * Get the store incharge dashboard.
     *
     * @param  int  $userId  The user identifier
     * @param  array  $filters  
     * @return array;  
     */
    public function getStoreInchargeDashboard(int $userId, array $filters = []): array;
    /**
     * Get the vendor dashboard.
     *
     * @param  int  $userId  The user identifier
     * @param  array  $filters  
     * @return array;  
     */
    public function getVendorDashboard(int $userId, array $filters = []): array;
}

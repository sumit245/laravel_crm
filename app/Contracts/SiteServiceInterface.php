<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Contract for site business logic. Defines methods for site management, pole association, target
 * tracking, and import operations.
 *
 * Data Flow:
 *   SiteController → SiteService (implements this) → Business rules → Repository →
 *   Model operations
 *
 * @business-domain Site Management
 * @package App\Contracts
 */
interface SiteServiceInterface extends ServiceInterface
{
    /**
     * Create site.
     *
     * @param  array  $data  The input data array
     * @return Model;  
     */
    public function createSite(array $data): Model;
    /**
     * Update site.
     *
     * @param  int  $siteId  The site identifier
     * @param  array  $data  The input data array
     * @return Model;  
     */
    public function updateSite(int $siteId, array $data): Model;
    /**
     * Delete site.
     *
     * @param  int  $siteId  The site identifier
     * @return bool;  
     */
    public function deleteSite(int $siteId): bool;
    /**
     * Update installation phase.
     *
     * @param  int  $siteId  The site identifier
     * @param  string  $phase  
     * @param  array  $data  The input data array
     * @return Model;  
     */
    public function updateInstallationPhase(int $siteId, string $phase, array $data = []): Model;
    /**
     * Assign engineer.
     *
     * @param  int  $siteId  The site identifier
     * @param  int  $engineerId  
     * @return Model;  
     */
    public function assignEngineer(int $siteId, int $engineerId): Model;
}

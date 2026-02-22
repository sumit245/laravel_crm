<?php

namespace App\Services\Site;

use App\Contracts\{SiteRepositoryInterface, SiteServiceInterface};
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service layer for site/panchayat operations. Handles site creation, pole management, and
 * site-level statistics aggregation.
 *
 * Data Flow:
 *   Controller delegates → Service handles site CRUD → Aggregates pole statistics →
 *   Returns structured data
 *
 * @depends-on Site, Streetlight, Pole
 * @business-domain Site Management
 * @package App\Services\Site
 */
class SiteManagementService extends BaseService implements SiteServiceInterface
{
    /**
     * Create a new SiteManagementService instance.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  SiteRepositoryInterface  $repository  
     */
    public function __construct(protected SiteRepositoryInterface $repository) {}

    /**
     * Create site.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function createSite(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            $site = $this->repository->create($data);
            $this->logInfo('Site created', ['site_id' => $site->id]);
            return $site;
        });
    }

    /**
     * Update site.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $siteId  The site identifier
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function updateSite(int $siteId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($siteId, $data) {
            $site = $this->repository->update($siteId, $data);
            $this->logInfo('Site updated', ['site_id' => $siteId]);
            return $site;
        });
    }

    /**
     * Delete site.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $siteId  The site identifier
     * @return bool  Success status
     */
    public function deleteSite(int $siteId): bool
    {
        return $this->executeInTransaction(function () use ($siteId) {
            $result = $this->repository->delete($siteId);
            $this->logInfo('Site deleted', ['site_id' => $siteId]);
            return $result;
        });
    }

    /**
     * Update installation phase.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $siteId  The site identifier
     * @param  string  $phase  
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function updateInstallationPhase(int $siteId, string $phase, array $data = []): Model
    {
        return $this->executeInTransaction(function () use ($siteId, $phase, $data) {
            $site = $this->repository->findById($siteId);
            $site->update(array_merge(['installation_status' => $phase], $data));
            $this->logInfo('Installation phase updated', ['site_id' => $siteId, 'phase' => $phase]);
            return $site;
        });
    }

    /**
     * Assign engineer.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $siteId  The site identifier
     * @param  int  $engineerId  
     * @return Model  
     */
    public function assignEngineer(int $siteId, int $engineerId): Model
    {
        return $this->executeInTransaction(function () use ($siteId, $engineerId) {
            $site = $this->repository->findById($siteId);
            $site->update(['site_engineer' => $engineerId]);
            $this->logInfo('Engineer assigned to site', ['site_id' => $siteId, 'engineer_id' => $engineerId]);
            return $site;
        });
    }
}

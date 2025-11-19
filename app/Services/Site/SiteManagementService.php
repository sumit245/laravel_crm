<?php

namespace App\Services\Site;

use App\Contracts\{SiteRepositoryInterface, SiteServiceInterface};
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

class SiteManagementService extends BaseService implements SiteServiceInterface
{
    public function __construct(protected SiteRepositoryInterface $repository) {}

    public function createSite(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            $site = $this->repository->create($data);
            $this->logInfo('Site created', ['site_id' => $site->id]);
            return $site;
        });
    }

    public function updateSite(int $siteId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($siteId, $data) {
            $site = $this->repository->update($siteId, $data);
            $this->logInfo('Site updated', ['site_id' => $siteId]);
            return $site;
        });
    }

    public function deleteSite(int $siteId): bool
    {
        return $this->executeInTransaction(function () use ($siteId) {
            $result = $this->repository->delete($siteId);
            $this->logInfo('Site deleted', ['site_id' => $siteId]);
            return $result;
        });
    }

    public function updateInstallationPhase(int $siteId, string $phase, array $data = []): Model
    {
        return $this->executeInTransaction(function () use ($siteId, $phase, $data) {
            $site = $this->repository->findById($siteId);
            $site->update(array_merge(['installation_status' => $phase], $data));
            $this->logInfo('Installation phase updated', ['site_id' => $siteId, 'phase' => $phase]);
            return $site;
        });
    }

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

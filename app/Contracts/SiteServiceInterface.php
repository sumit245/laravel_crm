<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Site Service Interface
 */
interface SiteServiceInterface extends ServiceInterface
{
    public function createSite(array $data): Model;
    public function updateSite(int $siteId, array $data): Model;
    public function deleteSite(int $siteId): bool;
    public function updateInstallationPhase(int $siteId, string $phase, array $data = []): Model;
    public function assignEngineer(int $siteId, int $engineerId): Model;
}

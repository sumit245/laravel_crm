<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Site Repository Interface
 */
interface SiteRepositoryInterface extends RepositoryInterface
{
    public function findByProject(int $projectId, array $with = []): Collection;
    public function findByDistrict(string $district, array $with = []): Collection;
    public function findByEngineer(int $engineerId, array $with = []): Collection;
    public function findByInstallationStatus(string $status, array $with = []): Collection;
    public function getSitesWithTasks(int $projectId): Collection;
    public function findWithFullRelations(int $id): ?Model;
}

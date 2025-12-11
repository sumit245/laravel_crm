<?php

namespace App\Services\Project;

use App\Contracts\ProjectRepositoryInterface;
use App\Contracts\ProjectServiceInterface;
use App\Enums\ProjectType;
use App\Enums\UserRole;
use App\Models\Project;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Project Service
 * 
 * Handles business logic for project management
 */
class ProjectService extends BaseService implements ProjectServiceInterface
{
    /**
     * ProjectService constructor
     */
    public function __construct(
        protected ProjectRepositoryInterface $projectRepository
    ) {}

    /**
     * {@inheritDoc}
     */
    public function createProject(array $data): Project
    {
        return $this->executeInTransaction(function () use ($data) {
            // Validate project data
            $this->validateProjectData($data);

            // Create the project
            $project = $this->projectRepository->create($data);

            $this->logInfo('Project created successfully', [
                'project_id' => $project->id,
                'work_order_number' => $project->work_order_number
            ]);

            return $project;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateProject(int $projectId, array $data): bool
    {
        return $this->executeInTransaction(function () use ($projectId, $data) {
            // Validate update data
            $this->validateProjectData($data, $projectId);

            // Update the project
            $result = $this->projectRepository->update($projectId, $data);

            if ($result) {
                $this->logInfo('Project updated successfully', [
                    'project_id' => $projectId
                ]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteProject(int $projectId): bool
    {
        return $this->executeInTransaction(function () use ($projectId) {
            $result = $this->projectRepository->delete($projectId);

            if ($result) {
                $this->logInfo('Project deleted successfully', [
                    'project_id' => $projectId
                ]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectWithRelations(int $projectId): ?Project
    {
        return $this->projectRepository->findWithFullRelations($projectId);
    }

    /**
     * {@inheritDoc}
     */
    public function assignStaffToProject(int $projectId, int $userId, string $role): bool
    {
        return $this->executeInTransaction(function () use ($projectId, $userId, $role) {
            $project = $this->projectRepository->findById($projectId);

            if (!$project) {
                throw new \Exception('Project not found');
            }

            // Attach user to project with role
            $project->users()->attach($userId, ['role' => $role]);

            $this->logInfo('Staff assigned to project', [
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => $role
            ]);

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function removeStaffFromProject(int $projectId, int $userId): bool
    {
        return $this->executeInTransaction(function () use ($projectId, $userId) {
            $project = $this->projectRepository->findById($projectId);

            if (!$project) {
                throw new \Exception('Project not found');
            }

            // Detach user from project
            $project->users()->detach($userId);

            $this->logInfo('Staff removed from project', [
                'project_id' => $projectId,
                'user_id' => $userId
            ]);

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectsForUser(int $userId, int $userRole): Collection
    {
        return $this->projectRepository->getAllForUser($userId, $userRole);
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectStatistics(int $projectId): array
    {
        $project = $this->projectRepository->findWithFullRelations($projectId);

        if (!$project) {
            return [];
        }

        $projectType = ProjectType::from($project->project_type);

        if ($projectType === ProjectType::STREETLIGHT) {
            return $this->getStreetlightProjectStatistics($project);
        }

        return $this->getRooftopProjectStatistics($project);
    }

    /**
     * Validate project data
     *
     * @param array $data
     * @param int|null $projectId For updates
     * @throws ValidationException
     */
    protected function validateProjectData(array $data, ?int $projectId = null): void
    {
        $rules = [
            'project_type' => 'required|in:0,1',
            'project_name' => 'required|string|max:255',
            'project_in_state' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'work_order_number' => 'required|string|unique:projects,work_order_number' . ($projectId ? ",$projectId" : ''),
            'rate' => 'nullable|numeric',
            'project_capacity' => 'nullable|string',
            'total' => 'nullable|numeric',
            'description' => 'nullable|string',
        ];

        // Add conditional validation for streetlight projects
        if (isset($data['project_type']) && $data['project_type'] == 1) {
            $rules['agreement_number'] = 'required|string';
            $rules['agreement_date'] = 'required|date';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get statistics for streetlight projects
     *
     * @param Project $project
     * @return array
     */
    protected function getStreetlightProjectStatistics(Project $project): array
    {
        $totalPoles = $project->streetlights()->sum('total_poles');
        $surveyedPoles = $project->streetlights()->sum('number_of_surveyed_poles');
        $installedPoles = $project->streetlights()->sum('number_of_installed_poles');

        return [
            'total_poles' => $totalPoles,
            'surveyed_poles' => $surveyedPoles,
            'installed_poles' => $installedPoles,
            'survey_percentage' => $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0,
            'installation_percentage' => $totalPoles > 0 ? ($installedPoles / $totalPoles) * 100 : 0,
        ];
    }

    /**
     * Get statistics for rooftop projects
     *
     * @param Project $project
     * @return array
     */
    protected function getRooftopProjectStatistics(Project $project): array
    {
        $totalSites = $project->sites()->count();
        $installationTasks = $project->tasks()->where('activity', 'Installation')->count();
        $rmsTasks = $project->tasks()->where('activity', 'RMS')->count();
        $inspectionTasks = $project->tasks()->where('activity', 'Inspection')->count();

        return [
            'total_sites' => $totalSites,
            'installation_tasks' => $installationTasks,
            'rms_tasks' => $rmsTasks,
            'inspection_tasks' => $inspectionTasks,
        ];
    }
}

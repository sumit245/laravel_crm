<?php

namespace App\Services\Task;

use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TaskStateMachineInterface;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Project;
use App\Models\StreetlightTask;
use App\Models\Streetlight;
use App\Models\Site;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Task Management Service
 * 
 * Handles all task business logic operations
 */
class TaskManagementService extends BaseService implements TaskServiceInterface
{
    /**
     * Create new TaskManagementService instance
     * 
     * @param TaskRepositoryInterface $repository
     * @param TaskStateMachineInterface $stateMachine
     */
    public function __construct(
        protected TaskRepositoryInterface $repository,
        protected TaskStateMachineInterface $stateMachine
    ) {
    }

    /**
     * Create a new task
     * 
     * @param array $data
     * @return Model
     */
    public function createTask(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            // Validate input
            $this->validateTaskCreation($data);

            // Prepare task data
            $taskData = [
                'project_id' => $data['project_id'],
                'site_id' => $data['site_id'] ?? null,
                'engineer_id' => $data['engineer_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'task_name' => $data['task_name'] ?? $data['activity'] ?? null,
                'activity' => $data['activity'] ?? $data['task_name'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => TaskStatus::PENDING->value,
                'start_date' => $data['start_date'] ?? now(),
                'end_date' => $data['end_date'] ?? null,
            ];

            // Create task
            $task = $this->repository->create($taskData);

            $this->logInfo('Task created successfully', ['task_id' => $task->id]);

            return $task;
        });
    }

    /**
     * Update existing task
     * 
     * @param int $taskId
     * @param array $data
     * @return Model
     */
    public function updateTask(int $taskId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $data) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Validate update data
            $this->validateTaskUpdate($data);

            // Update task
            $updatedTask = $this->repository->update($taskId, $data);

            $this->logInfo('Task updated successfully', ['task_id' => $taskId]);

            return $updatedTask;
        });
    }

    /**
     * Assign engineer to task
     * 
     * @param int $taskId
     * @param int $engineerId
     * @return Model
     */
    public function assignEngineer(int $taskId, int $engineerId): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $engineerId) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Validate engineer
            $engineer = User::find($engineerId);
            if (!$engineer) {
                throw new InvalidArgumentException("Engineer with ID {$engineerId} not found");
            }

            $engineerRole = UserRole::from((int) $engineer->role);
            if (!$engineerRole->isFieldRole()) {
                throw new InvalidArgumentException("User is not a valid field engineer");
            }

            // Assign engineer
            $task->engineer_id = $engineerId;
            $task->save();

            $this->logInfo('Engineer assigned to task', [
                'task_id' => $taskId,
                'engineer_id' => $engineerId
            ]);

            return $task;
        });
    }

    /**
     * Assign vendor to task
     * 
     * @param int $taskId
     * @param int $vendorId
     * @return Model
     */
    public function assignVendor(int $taskId, int $vendorId): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $vendorId) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Validate vendor
            $vendor = User::find($vendorId);
            if (!$vendor) {
                throw new InvalidArgumentException("Vendor with ID {$vendorId} not found");
            }

            $vendorRole = UserRole::from((int) $vendor->role);
            if ($vendorRole !== UserRole::VENDOR) {
                throw new InvalidArgumentException("User is not a valid vendor");
            }

            // Assign vendor
            $task->vendor_id = $vendorId;
            $task->save();

            $this->logInfo('Vendor assigned to task', [
                'task_id' => $taskId,
                'vendor_id' => $vendorId
            ]);

            return $task;
        });
    }

    /**
     * Update task status with state machine validation
     * 
     * @param int $taskId
     * @param TaskStatus $newStatus
     * @param array $additionalData
     * @return Model
     */
    public function updateTaskStatus(int $taskId, TaskStatus $newStatus, array $additionalData = []): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $newStatus, $additionalData) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Use state machine to execute transition
            $updatedTask = $this->stateMachine->executeTransition($task, $newStatus, $additionalData);

            $this->logInfo('Task status updated', [
                'task_id' => $taskId,
                'new_status' => $newStatus->value
            ]);

            return $updatedTask;
        });
    }

    /**
     * Record task progress
     * 
     * @param int $taskId
     * @param array $progressData
     * @return Model
     */
    public function recordProgress(int $taskId, array $progressData): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $progressData) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Update progress information
            if (isset($progressData['description'])) {
                $task->description = $progressData['description'];
            }

            if (isset($progressData['image'])) {
                $task->image = $progressData['image'];
            }

            if (isset($progressData['materials_consumed'])) {
                $task->materials_consumed = $progressData['materials_consumed'];
            }

            $task->save();

            $this->logInfo('Task progress recorded', ['task_id' => $taskId]);

            return $task;
        });
    }

    /**
     * Cancel task
     * 
     * @param int $taskId
     * @param string $reason
     * @param int $cancelledBy
     * @return Model
     */
    public function cancelTask(int $taskId, string $reason, int $cancelledBy): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $reason, $cancelledBy) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Add cancellation note
            $task->description = "CANCELLED: {$reason}\n\n" . ($task->description ?? '');
            $task->save();

            $this->logWarning('Task cancelled', [
                'task_id' => $taskId,
                'reason' => $reason,
                'cancelled_by' => $cancelledBy
            ]);

            return $task;
        });
    }

    /**
     * Reassign task to different engineer
     * 
     * @param int $taskId
     * @param int $newEngineerId
     * @param string|null $reason
     * @return Model
     */
    public function reassignTask(int $taskId, int $newEngineerId, ?string $reason = null): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $newEngineerId, $reason) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            $oldEngineerId = $task->engineer_id;

            // Validate new engineer
            $engineer = User::find($newEngineerId);
            if (!$engineer) {
                throw new InvalidArgumentException("Engineer with ID {$newEngineerId} not found");
            }

            // Reassign
            $task->engineer_id = $newEngineerId;

            if ($reason) {
                $task->description = "REASSIGNED: {$reason}\n\n" . ($task->description ?? '');
            }

            $task->save();

            $this->logInfo('Task reassigned', [
                'task_id' => $taskId,
                'old_engineer_id' => $oldEngineerId,
                'new_engineer_id' => $newEngineerId,
                'reason' => $reason
            ]);

            return $task;
        });
    }

    /**
     * Escalate blocked task to manager
     * 
     * @param int $taskId
     * @param string $escalationReason
     * @return Model
     */
    public function escalateTask(int $taskId, string $escalationReason): Model
    {
        return $this->executeInTransaction(function () use ($taskId, $escalationReason) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            // Add escalation note
            $task->description = "ESCALATED: {$escalationReason}\n\n" . ($task->description ?? '');
            $task->save();

            $this->logWarning('Task escalated', [
                'task_id' => $taskId,
                'reason' => $escalationReason,
                'manager_id' => $task->manager_id
            ]);

            return $task;
        });
    }

    /**
     * Delete task
     * 
     * @param int $taskId
     * @return bool
     */
    public function deleteTask(int $taskId): bool
    {
        return $this->executeInTransaction(function () use ($taskId) {
            $task = $this->repository->findById($taskId);

            if (!$task) {
                throw new InvalidArgumentException("Task with ID {$taskId} not found");
            }

            $result = $this->repository->delete($taskId);

            $this->logInfo('Task deleted', ['task_id' => $taskId]);

            return $result;
        });
    }

    /**
     * Validate task creation data
     * 
     * @param array $data
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateTaskCreation(array $data): void
    {
        $validator = Validator::make($data, [
            'project_id' => 'required|exists:projects,id',
            'site_id' => 'nullable|exists:sites,id',
            'engineer_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
            'task_name' => 'nullable|string|max:255',
            'activity' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Validation failed: ' . implode(', ', $validator->errors()->all())
            );
        }
    }

    /**
     * Validate task update data
     * 
     * @param array $data
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateTaskUpdate(array $data): void
    {
        $rules = [];

        if (isset($data['project_id'])) {
            $rules['project_id'] = 'exists:projects,id';
        }

        if (isset($data['site_id'])) {
            $rules['site_id'] = 'exists:sites,id';
        }

        if (isset($data['engineer_id'])) {
            $rules['engineer_id'] = 'exists:users,id';
        }

        if (isset($data['vendor_id'])) {
            $rules['vendor_id'] = 'exists:users,id';
        }

        if (isset($data['end_date']) && isset($data['start_date'])) {
            $rules['end_date'] = 'date|after_or_equal:start_date';
        }

        if (!empty($rules)) {
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new InvalidArgumentException(
                    'Validation failed: ' . implode(', ', $validator->errors()->all())
                );
            }
        }
    }

    /**
     * Create bulk tasks for multiple sites
     * 
     * @param int $projectId
     * @param array $siteIds
     * @param array $taskData
     * @param int $createdBy
     * @return void
     */
    public function createBulkTasks(int $projectId, array $siteIds, array $taskData, int $createdBy): void
    {
        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:447', 'message' => 'createBulkTasks entry', 'data' => ['project_id' => $projectId, 'site_ids_count' => count($siteIds), 'site_ids' => $siteIds, 'task_data_keys' => array_keys($taskData), 'created_by' => $createdBy], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        $this->executeInTransaction(function () use ($projectId, $siteIds, $taskData, $createdBy) {
            $project = Project::findOrFail($projectId);

            // #region agent log
            file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:454', 'message' => 'project found', 'data' => ['project_type' => $project->project_type], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
            // #endregion

            // Handle streetlight projects (project_type == 1)
            if ($project->project_type == 1) {
                // #region agent log
                file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:459', 'message' => 'streetlight project detected', 'data' => [], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                // #endregion

                foreach ($siteIds as $siteId) {
                    // #region agent log
                    file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:463', 'message' => 'processing site', 'data' => ['site_id' => $siteId], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                    // #endregion

                    // Check if task already exists for this site
                    $existingTask = StreetlightTask::where('site_id', $siteId)->first();
                    if ($existingTask) {
                        // #region agent log
                        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:468', 'message' => 'task already exists, skipping', 'data' => ['site_id' => $siteId, 'existing_task_id' => $existingTask->id], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                        // #endregion
                        continue;
                    }

                    // Verify streetlight site exists
                    $streetlight = Streetlight::find($siteId);
                    if (!$streetlight) {
                        // #region agent log
                        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:476', 'message' => 'streetlight site not found', 'data' => ['site_id' => $siteId], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                        // #endregion
                        throw new InvalidArgumentException("Streetlight site with ID {$siteId} not found");
                    }

                    // Prepare task data for streetlight
                    $streetlightTaskData = [
                        'project_id' => $projectId,
                        'site_id' => $siteId,
                        'engineer_id' => $taskData['engineer_id'] ?? null,
                        'vendor_id' => $taskData['vendor_id'] ?? null,
                        'manager_id' => $taskData['manager_id'] ?? $createdBy,
                        'status' => TaskStatus::PENDING->value,
                        'start_date' => $taskData['start_date'] ?? now(),
                        'end_date' => $taskData['end_date'] ?? null,
                    ];

                    // #region agent log
                    file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:492', 'message' => 'before creating streetlight task', 'data' => ['task_data' => $streetlightTaskData], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                    // #endregion

                    StreetlightTask::create($streetlightTaskData);

                    // #region agent log
                    file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:496', 'message' => 'streetlight task created', 'data' => ['site_id' => $siteId], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                    // #endregion
                }
            } else {
                // Handle rooftop projects (project_type == 0)
                // #region agent log
                file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:502', 'message' => 'rooftop project detected', 'data' => [], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                // #endregion

                foreach ($siteIds as $siteId) {
                    $taskData['site_id'] = $siteId;
                    $this->createTask($taskData);
                }
            }

            // #region agent log
            file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TaskManagementService.php:512', 'message' => 'createBulkTasks completed', 'data' => ['sites_processed' => count($siteIds)], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
            // #endregion
        });
    }

    /**
     * Get tasks by project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasksByProject(int $projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->project_type == 1) {
            // Streetlight project
            return StreetlightTask::with(['engineer', 'vendor', 'manager', 'site'])
                ->where('project_id', $projectId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Rooftop project
        return $this->repository->findByProject($projectId);
    }

    /**
     * Get task details by ID
     * 
     * @param int $taskId
     * @param int|null $projectType
     * @return array
     */
    public function getTaskDetails(int $taskId, ?int $projectType = null): array
    {
        if ($projectType == 1) {
            $task = StreetlightTask::with(['engineer', 'vendor', 'manager', 'site', 'poles'])
                ->findOrFail($taskId);

            return [
                'task' => $task,
                'poles' => $task->poles,
            ];
        }

        $task = $this->repository->findWithFullRelations($taskId);
        return ['task' => $task];
    }

    /**
     * Find task by ID
     * 
     * @param int $taskId
     * @return Model|null
     */
    public function findById(int $taskId): ?Model
    {
        return $this->repository->findById($taskId);
    }

    /**
     * Get available engineers for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableEngineers(int $projectId)
    {
        return User::whereHas('projects', function ($query) use ($projectId) {
            $query->where('projects.id', $projectId);
        })
            ->whereIn('role', [1, 2]) // Site Engineer, Project Manager
            ->get();
    }

    /**
     * Get available vendors for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableVendors(int $projectId)
    {
        return User::whereHas('projects', function ($query) use ($projectId) {
            $query->where('projects.id', $projectId);
        })
            ->where('role', 3) // Vendor
            ->get();
    }

    /**
     * Get available sites for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableSites(int $projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->project_type == 1) {
            return Streetlight::where('project_id', $projectId)->get();
        }

        return Site::where('project_id', $projectId)->get();
    }
}

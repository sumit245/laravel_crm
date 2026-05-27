<?php

namespace App\Services\Notification;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;

class StakeholderResolver
{
    /**
     * Resolve user IDs for an event based on role and project/district scope.
     *
     * @param  int|null  $projectId
     * @param  int|null  $districtId
     * @return Collection<int, int>
     */
    public function resolve(?int $projectId, ?int $districtId): Collection
    {
        $adminIds = User::query()
            ->where('role', UserRole::ADMIN->value)
            ->pluck('id');

        if (!$projectId) {
            return $adminIds->values()->unique();
        }

        $scopedAssignments = User::query()
            ->select('users.id')
            ->join('project_user', 'project_user.user_id', '=', 'users.id')
            ->where('project_user.project_id', $projectId)
            ->whereIn('users.role', [
                UserRole::PROJECT_MANAGER->value,
                UserRole::COORDINATOR->value,
            ])
            ->when($districtId, function ($query) use ($districtId) {
                $query->where('project_user.district_id', $districtId);
            })
            ->pluck('users.id');

        return $adminIds
            ->merge($scopedAssignments)
            ->unique()
            ->values();
    }
}

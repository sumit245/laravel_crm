<?php

namespace App\Repositories\Meeting;

use App\Contracts\MeetingRepositoryInterface;
use App\Models\Meet;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MeetingRepository extends BaseRepository implements MeetingRepositoryInterface
{
    public function __construct(Meet $model)
    {
        parent::__construct($model);
    }

    public function findByDateRange(string $startDate, string $endDate, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->whereBetween('meet_date', [$startDate, $endDate])
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    public function findByParticipant(int $userId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    public function findByProject(int $projectId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->where('project_id', $projectId)
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    public function findUpcomingMeetings(?int $userId = null): Collection
    {
        $query = $this->model->with(['participants', 'discussionPoints'])
            ->where('meet_date', '>=', now())
            ->orderBy('meet_date', 'asc');
        
        if ($userId) {
            $query->whereHas('participants', fn($q) => $q->where('user_id', $userId));
        }
        
        return $query->get();
    }

    public function findPastMeetings(?int $userId = null, int $limit = 10): Collection
    {
        $query = $this->model->with(['participants', 'discussionPoints'])
            ->where('meet_date', '<', now())
            ->orderBy('meet_date', 'desc')
            ->limit($limit);
        
        if ($userId) {
            $query->whereHas('participants', fn($q) => $q->where('user_id', $userId));
        }
        
        return $query->get();
    }

    public function findWithPendingActionItems(?int $projectId = null): Collection
    {
        $query = $this->model->with(['participants', 'discussionPoints' => fn($q) => $q->where('status', '!=', 'Completed')]);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->whereHas('discussionPoints', fn($q) => $q->where('status', '!=', 'Completed'))
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    public function findWithFullRelations(int $id): ?Model
    {
        return $this->model->with(['participants', 'discussionPoints', 'notesHistory', 'whiteboard', 'followUps'])
            ->find($id);
    }
}

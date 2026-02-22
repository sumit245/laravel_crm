<?php

namespace App\Repositories\Meeting;

use App\Contracts\MeetingRepositoryInterface;
use App\Models\Meet;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Data access layer for meetings. Provides optimized queries for fetching meetings with
 * discussion points, filtering by date/project, and aggregating meeting statistics.
 *
 * Data Flow:
 *   MeetingService → MeetingRepository → Optimized Eloquent queries → Meet +
 *   DiscussionPoint + FollowUp data
 *
 * @depends-on Meet, DiscussionPoint, FollowUp
 * @business-domain Meetings & Collaboration
 * @package App\Repositories\Meeting
 */
class MeetingRepository extends BaseRepository implements MeetingRepositoryInterface
{
    /**
     * Create a new MeetingRepository instance.
     *
     * @param  Meet  $model  
     */
    public function __construct(Meet $model)
    {
        parent::__construct($model);
    }

    /**
     * Find by date range.
     *
     * @param  string  $startDate  
     * @param  string  $endDate  
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByDateRange(string $startDate, string $endDate, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->whereBetween('meet_date', [$startDate, $endDate])
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    /**
     * Find by participant.
     *
     * @param  int  $userId  The user identifier
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByParticipant(int $userId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    /**
     * Find by project.
     *
     * @param  int  $projectId  The project identifier
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByProject(int $projectId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['participants', 'discussionPoints'])
            ->where('project_id', $projectId)
            ->orderBy('meet_date', 'desc')
            ->get();
    }

    /**
     * Find upcoming meetings.
     *
     * @param  ?int  $userId  The user identifier
     * @return Collection  Collection of results
     */
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

    /**
     * Find past meetings.
     *
     * @param  ?int  $userId  The user identifier
     * @param  int  $limit  The number of records to return
     * @return Collection  Collection of results
     */
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

    /**
     * Find with pending action items.
     *
     * @param  ?int  $projectId  The project identifier
     * @return Collection  Collection of results
     */
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

    /**
     * Find with full relations.
     *
     * @param  int  $id  The resource identifier
     * @return ?Model  
     */
    public function findWithFullRelations(int $id): ?Model
    {
        return $this->model->with(['participants', 'discussionPoints', 'notesHistory', 'whiteboard', 'followUps'])
            ->find($id);
    }
}

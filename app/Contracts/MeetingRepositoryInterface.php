<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for meeting data access. Defines methods for querying meetings with filters, fetching
 * meetings with discussion points, and aggregating meeting statistics.
 *
 * Data Flow:
 *   MeetingService → MeetingRepository (implements this) → Eloquent queries → Meet +
 *   DiscussionPoint data
 *
 * @business-domain Meetings & Collaboration
 * @package App\Contracts
 */
interface MeetingRepositoryInterface extends RepositoryInterface
{
    /**
     * Find meetings by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $with
     * @return Collection
     */
    public function findByDateRange(string $startDate, string $endDate, array $with = []): Collection;

    /**
     * Find meetings by participant
     * 
     * @param int $userId
     * @param array $with
     * @return Collection
     */
    public function findByParticipant(int $userId, array $with = []): Collection;

    /**
     * Find meetings by project
     * 
     * @param int $projectId
     * @param array $with
     * @return Collection
     */
    public function findByProject(int $projectId, array $with = []): Collection;

    /**
     * Find upcoming meetings
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function findUpcomingMeetings(?int $userId = null): Collection;

    /**
     * Find past meetings
     * 
     * @param int|null $userId
     * @param int $limit
     * @return Collection
     */
    public function findPastMeetings(?int $userId = null, int $limit = 10): Collection;

    /**
     * Find meetings with pending discussion points
     * 
     * @param int|null $projectId
     * @return Collection
     */
    public function findWithPendingActionItems(?int $projectId = null): Collection;

    /**
     * Find meeting with full relationships
     * 
     * @param int $id
     * @return Model|null
     */
    public function findWithFullRelations(int $id): ?Model;
}

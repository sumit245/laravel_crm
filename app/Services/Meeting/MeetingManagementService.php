<?php

namespace App\Services\Meeting;

use App\Contracts\{MeetingRepositoryInterface, MeetingServiceInterface};
use App\Models\{Meet, DiscussionPoint, MeetingNoteHistory};
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service layer for meeting operations. Handles meeting CRUD, discussion point management,
 * attendee management, follow-up scheduling, and notification dispatch.
 *
 * Data Flow:
 *   Controller delegates → Service validates business rules → Model operations →
 *   Notification dispatch
 *
 * @depends-on Meet, DiscussionPoint, FollowUp, User
 * @business-domain Meetings & Collaboration
 * @package App\Services\Meeting
 */
class MeetingManagementService extends BaseService implements MeetingServiceInterface
{
    /**
     * Create a new MeetingManagementService instance.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  MeetingRepositoryInterface  $repository  
     */
    public function __construct(protected MeetingRepositoryInterface $repository) {}

    /**
     * Create meeting.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function createMeeting(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            $meeting = $this->repository->create([
                'title' => $data['title'],
                'agenda' => $data['agenda'] ?? null,
                'meet_link' => $data['meet_link'] ?? null,
                'platform' => $data['platform'] ?? null,
                'meet_date' => $data['meet_date'],
                'meet_time' => $data['meet_time'] ?? null,
                'type' => $data['type'] ?? 'internal',
                'notes' => $data['notes'] ?? null,
            ]);

            if (!empty($data['participants'])) {
                $meeting->participants()->sync($data['participants']);
            }

            $this->logInfo('Meeting created', ['meeting_id' => $meeting->id]);
            return $meeting;
        });
    }

    /**
     * Update meeting.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $meetingId  
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function updateMeeting(int $meetingId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $data) {
            $meeting = $this->repository->update($meetingId, $data);
            $this->logInfo('Meeting updated', ['meeting_id' => $meetingId]);
            return $meeting;
        });
    }

    /**
     * Delete meeting.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $meetingId  
     * @return bool  Success status
     */
    public function deleteMeeting(int $meetingId): bool
    {
        return $this->executeInTransaction(function () use ($meetingId) {
            $result = $this->repository->delete($meetingId);
            $this->logInfo('Meeting deleted', ['meeting_id' => $meetingId]);
            return $result;
        });
    }

    /**
     * Add participants.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $meetingId  
     * @param  array  $userIds  
     * @return Model  
     */
    public function addParticipants(int $meetingId, array $userIds): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $userIds) {
            $meeting = $this->repository->findById($meetingId);
            $meeting->participants()->syncWithoutDetaching($userIds);
            return $meeting->fresh(['participants']);
        });
    }

    /**
     * Save notes.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $meetingId  
     * @param  string  $notes  
     * @param  int  $userId  The user identifier
     * @return Model  
     */
    public function saveNotes(int $meetingId, string $notes, int $userId): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $notes, $userId) {
            $meeting = $this->repository->findById($meetingId);
            
            MeetingNoteHistory::create([
                'meet_id' => $meetingId,
                'user_id' => $userId,
                'notes' => $notes,
                'version' => $meeting->notesHistory()->count() + 1,
            ]);
            
            $meeting->update(['notes' => $notes]);
            return $meeting->fresh(['notesHistory']);
        });
    }

    /**
     * Create discussion point.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $meetingId  
     * @param  array  $data  The input data array
     * @return Model  
     */
    public function createDiscussionPoint(int $meetingId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $data) {
            return DiscussionPoint::create([
                'meet_id' => $meetingId,
                'point' => $data['point'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'status' => $data['status'] ?? 'Pending',
                'due_date' => $data['due_date'] ?? null,
                'priority' => $data['priority'] ?? 'Medium',
            ]);
        });
    }

    /**
     * Update discussion status.
     *
     * Data flow: Called by Controller → Database interaction → Returns result
     *
     * @param  int  $discussionPointId  
     * @param  string  $status  The status value
     * @param  ?string  $notes  
     * @return Model  
     */
    public function updateDiscussionStatus(int $discussionPointId, string $status, ?string $notes = null): Model
    {
        return $this->executeInTransaction(function () use ($discussionPointId, $status, $notes) {
            $point = DiscussionPoint::findOrFail($discussionPointId);
            $point->update([
                'status' => $status,
                'notes' => $notes ?? $point->notes,
            ]);
            return $point;
        });
    }
}

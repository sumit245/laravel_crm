<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Meeting Service Interface
 * 
 * Defines contract for meeting business logic operations
 */
interface MeetingServiceInterface extends ServiceInterface
{
    /**
     * Create a new meeting
     * 
     * @param array $data
     * @return Model
     */
    public function createMeeting(array $data): Model;

    /**
     * Update meeting
     * 
     * @param int $meetingId
     * @param array $data
     * @return Model
     */
    public function updateMeeting(int $meetingId, array $data): Model;

    /**
     * Delete meeting
     * 
     * @param int $meetingId
     * @return bool
     */
    public function deleteMeeting(int $meetingId): bool;

    /**
     * Add participants to meeting
     * 
     * @param int $meetingId
     * @param array $userIds
     * @return Model
     */
    public function addParticipants(int $meetingId, array $userIds): Model;

    /**
     * Save meeting notes
     * 
     * @param int $meetingId
     * @param string $notes
     * @param int $userId
     * @return Model
     */
    public function saveNotes(int $meetingId, string $notes, int $userId): Model;

    /**
     * Create discussion point
     * 
     * @param int $meetingId
     * @param array $data
     * @return Model
     */
    public function createDiscussionPoint(int $meetingId, array $data): Model;

    /**
     * Update discussion point status
     * 
     * @param int $discussionPointId
     * @param string $status
     * @param string|null $notes
     * @return Model
     */
    public function updateDiscussionStatus(int $discussionPointId, string $status, ?string $notes = null): Model;
}

<?php

namespace App\Services\Meeting;

use App\Contracts\{MeetingRepositoryInterface, MeetingServiceInterface};
use App\Models\{Meet, DiscussionPoint, MeetingNoteHistory};
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

class MeetingManagementService extends BaseService implements MeetingServiceInterface
{
    public function __construct(protected MeetingRepositoryInterface $repository) {}

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

    public function updateMeeting(int $meetingId, array $data): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $data) {
            $meeting = $this->repository->update($meetingId, $data);
            $this->logInfo('Meeting updated', ['meeting_id' => $meetingId]);
            return $meeting;
        });
    }

    public function deleteMeeting(int $meetingId): bool
    {
        return $this->executeInTransaction(function () use ($meetingId) {
            $result = $this->repository->delete($meetingId);
            $this->logInfo('Meeting deleted', ['meeting_id' => $meetingId]);
            return $result;
        });
    }

    public function addParticipants(int $meetingId, array $userIds): Model
    {
        return $this->executeInTransaction(function () use ($meetingId, $userIds) {
            $meeting = $this->repository->findById($meetingId);
            $meeting->participants()->syncWithoutDetaching($userIds);
            return $meeting->fresh(['participants']);
        });
    }

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

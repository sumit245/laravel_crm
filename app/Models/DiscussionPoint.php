<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Individual agenda/discussion point within a meeting. Has status lifecycle (Open → In Progress
 * → Resolved), priority, and assigned owner. Supports updates/comments over time.
 *
 * Data Flow:
 *   Created in meeting → Status tracked → Updates added → Resolved or carried to
 *   follow-up meeting
 *
 * @depends-on Meet, User, DiscussionPointUpdates
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class DiscussionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'meet_id',
        'project_id',
        'title',
        'description',
        'assignee_id',
        'assigned_to',
        'department',
        'priority',
        'status',
        'due_date',
    ];

    /**
     * Meet.
     *
     * @return void  
     */
    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }

    /**
     * Assignee.
     *
     * @return void  
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Assigned to user.
     *
     * @return void  
     */
    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Many-to-many relationship with users (multiple assignees)
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'discussion_point_user')
            ->withTimestamps();
    }

    /**
     * Updates.
     *
     * @return void  
     */
    public function updates()
    {
        return $this->hasMany(DiscussionPointUpdates::class)->latest();
    }

    /**
     * Project.
     *
     * @return void  
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

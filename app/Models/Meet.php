<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Whiteboard;

/**
 * Formal meeting record with type (Internal/Client/Government), date, location, and attendee
 * tracking. Contains meeting notes and links to discussion points and follow-ups.
 *
 * Data Flow:
 *   Create meeting → Add attendees → Record discussion points → Track point status →
 *   Schedule follow-ups → Export minutes as PDF/Excel
 *
 * @depends-on User, DiscussionPoint, FollowUp, Project
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class Meet extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'agenda',
        'meet_link',
        'platform',
        'meet_date',
        'meet_time',
        'type',
        'notes',
    ];

    protected $casts = [
        'meet_date' => 'date',
        // meet_time is stored as TIME type in DB, no cast needed - Laravel handles it as string
    ];

    /**
     * Whiteboard.
     *
     * @return void  
     */
    public function whiteboard()
    {
        return $this->hasOne(Whiteboard::class);
    }
    /**
     * app/Models/Meet.php
     *
     * @return void  
     */
    public function notesHistory()
    {
        return $this->hasMany(MeetingNoteHistory::class)->latest(); // Order by most recent
    }

    /**
     * Participants.
     *
     * @return void  
     */
    public function participants()
    {
        // Make sure you have this relationship defined
        return $this->belongsToMany(User::class, 'meet_user');
    }
    /**
     * Attendees.
     *
     * @return void  
     */
    public function attendees()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * app/Models/MeetingNoteHistory.php
     *
     * @return void  
     */
    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }

    /**
     * User.
     *
     * @return void  
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Discussion points.
     *
     * @return void  
     */
    public function discussionPoints()
    {
        return $this->hasMany(DiscussionPoint::class)->orderBy('created_at', 'desc');
    }

    /**
     * Follow ups.
     *
     * @return void  
     */
    public function followUps()
    {
        return $this->hasMany(FollowUp::class, 'parent_meet_id');
    }

}

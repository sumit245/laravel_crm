<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Meeting follow-up record. Links to a discussion point from a previous meeting that needs
 * continued attention. Tracks follow-up date, assigned person, and completion status.
 *
 * Data Flow:
 *   Discussion point unresolved → Create follow-up → Assign to next meeting → Track
 *   until resolved
 *
 * @depends-on Meet, DiscussionPoint
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = ['parent_meet_id', 'meet_id', 'title', 'meet_date', 'status'];

    /**
     * Parent meet.
     *
     * @return void  
     */
    public function parentMeet()
    {
        return $this->belongsTo(Meet::class, 'parent_meet_id');
    }

    /**
     * Meet.
     *
     * @return void  
     */
    public function meet()
    {
        return $this->belongsTo(Meet::class, 'meet_id');
    }
}

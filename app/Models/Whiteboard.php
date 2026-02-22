<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared whiteboard/notes content model. Stores collaborative notes and action items created
 * during project planning sessions. Supports rich text and tagging.
 *
 * Data Flow:
 *   Create whiteboard → Add content → Share with team → Edit collaboratively →
 *   Reference in meetings
 *
 * @depends-on User, Project
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class Whiteboard extends Model
{
    use HasFactory;
     protected $fillable = ['review_meeting_id', 'data'];
     /**
      * Review meeting.
      *
      * @return void  
      */
     public function reviewMeeting()
    {
        return $this->belongsTo(ReviewMeeting::class);
    }
}

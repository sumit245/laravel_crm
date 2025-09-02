<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Whiteboard extends Model
{
    use HasFactory;
     protected $fillable = ['review_meeting_id', 'data'];
     public function reviewMeeting()
    {
        return $this->belongsTo(ReviewMeeting::class);
    }
}

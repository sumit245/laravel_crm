<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Whiteboard;

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
        'user_ids',
        'notes',
    ];

    protected $casts = [
        'user_ids' => 'array', // Automatically casts JSON to array
        'meet_date' => 'date',
        'meet_time' => 'datetime:H:i',
    ];

     public function whiteboard()
    {
        return $this->hasOne(Whiteboard::class);
    }
    // app/Models/Meet.php
public function notesHistory()
{
    return $this->hasMany(MeetingNoteHistory::class)->latest(); // Order by most recent
}

public function participants()
{
    // Make sure you have this relationship defined
    return $this->belongsToMany(User::class, 'meet_user'); 
}

// app/Models/MeetingNoteHistory.php
public function meet()
{
    return $this->belongsTo(Meet::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}

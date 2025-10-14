<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'meet_id',
        'title',
        'description',
        'assignee_id',
        'assigned_to',
        'department',
        'priority',
        'status',
        'due_date',
    ];

    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates()
    {
        return $this->hasMany(DiscussionPointUpdates::class)->latest();
    }
}

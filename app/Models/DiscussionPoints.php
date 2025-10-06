<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionPoints extends Model
{
    use HasFactory;

    protected $fillable = [
        'meet_id',
        'title',
        'description',
        'assignee_id',
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

    public function updates()
    {
        return $this->hasMany(DiscussionPointUpdates::class)->latest();
    }
}

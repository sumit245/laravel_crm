<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionPointUpdates extends Model
{
    use HasFactory;

    protected $fillable = ['discussion_point_id', 'update_text'];

    public function discussionPoint()
    {
        return $this->belongsTo(DiscussionPoints::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionPointUpdates extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'discussion_points_updates';
    protected $fillable = ['discussion_point_id', 'update_text'];

    public function discussionPoint()
    {
        return $this->belongsTo(DiscussionPoint::class, 'discussion_point_id');
    }
}

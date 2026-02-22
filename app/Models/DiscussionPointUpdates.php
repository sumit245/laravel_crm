<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Track status changes and comments on meeting discussion points over time. Each update records
 * who changed what, when, and any notes — providing a full change history for each agenda item.
 *
 * Data Flow:
 *   Discussion point status changed → Create update record → Display in timeline under
 *   discussion point → Available for audit
 *
 * @depends-on DiscussionPoint, User
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class DiscussionPointUpdates extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'discussion_points_updates';
    protected $fillable = ['discussion_point_id', 'update_text', 'vertical_head_remark', 'admin_remark'];

    /**
     * Discussion point.
     *
     * @return void  
     */
    public function discussionPoint()
    {
        return $this->belongsTo(DiscussionPoint::class, 'discussion_point_id');
    }
}

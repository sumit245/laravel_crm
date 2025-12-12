<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = ['parent_meet_id', 'meet_id', 'title', 'meet_date', 'status'];

    public function parentMeet()
    {
        return $this->belongsTo(Meet::class, 'parent_meet_id');
    }

    public function meet()
    {
        return $this->belongsTo(Meet::class, 'meet_id');
    }
}

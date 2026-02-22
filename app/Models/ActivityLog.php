<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail record for system actions. Captures who did what, when, and to which entity. Used
 * for accountability and compliance tracking.
 *
 * Data Flow:
 *   System action triggers → ActivityLogger creates record → Admin views audit trail →
 *   Filter by entity/user/date
 *
 * @depends-on User
 * @business-domain Audit & Compliance
 * @package App\Models
 */
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Guard all attributes by default; we'll use explicit create() with arrays.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'changes' => 'array',
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User.
     *
     * @return BelongsTo  
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project.
     *
     * @return BelongsTo  
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

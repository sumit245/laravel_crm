<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Work assignment linking a panchayat/site to field staff (engineer + vendor + PM). Contains
 * assignment dates, status (Pending/In Progress/Completed), and relationship to poles. This is
 * how field work is assigned and tracked.
 *
 * Data Flow:
 *   Admin assigns site → Create task record → Field app fetches tasks → Survey + Install
 *   poles → Update status → Complete when all poles done
 *
 * @depends-on Streetlight, User, Pole, Project
 * @business-domain Field Operations
 * @package App\Models
 */
class StreetlightTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'site_id',
        'engineer_id',
        'manager_id',
        'vendor_id',
        'status',
        'start_date',
        'end_date',
        'materials_consumed',
        'approved_by',
        'billed',
    ];

    /**
     * Get the project this task belongs to
     * Relationship: StreetlightTask belongs to Project
     * Foreign Key: streetlight_tasks.project_id → projects.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the streetlight site this task belongs to
     * Relationship: StreetlightTask belongs to Streetlight (site)
     * Foreign Key: streetlight_tasks.site_id → streetlights.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Streetlight::class, 'site_id');
    }

    /**
     * Get the engineer assigned to this task
     * Relationship: StreetlightTask belongs to User (Site Engineer)
     * Foreign Key: streetlight_tasks.engineer_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id', 'id');
    }

    /**
     * Get the vendor assigned to this task
     * Relationship: StreetlightTask belongs to User (Vendor)
     * Foreign Key: streetlight_tasks.vendor_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id');
    }

    /**
     * Get the project manager assigned to this task
     * Relationship: StreetlightTask belongs to User (Project Manager)
     * Foreign Key: streetlight_tasks.manager_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    /**
     * Get all poles associated with this task
     * Relationship: StreetlightTask has many Poles
     * Foreign Key: streelight_poles.task_id → streetlight_tasks.id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function poles()
    {
        return $this->hasMany(Pole::class, 'task_id');
    }

    /**
     * Get the streetlight site this task belongs to (alias for site())
     * Relationship: StreetlightTask belongs to Streetlight (site)
     * Foreign Key: streetlight_tasks.site_id → streetlights.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function streetlight()
    {
        return $this->belongsTo(Streetlight::class, 'site_id');
    }
}

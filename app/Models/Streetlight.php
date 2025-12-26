<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streetlight extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_id',
        'state',
        'district',
        'block',
        'panchayat',
        'ward',
        'project_id',
        'total_poles',
        'mukhiya_contact',
        'number_of_surveyed_poles',
        'number_of_installed_poles',
        'district_code',
        'block_code',
        'panchayat_code',
        'ward_type',
    ];

    /**
     * Get all poles associated with this streetlight site
     * Relationship is indirect: Streetlight → StreetlightTask → Pole
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function poles()
    {
        // Indirect relationship - poles belong to tasks, tasks belong to this site
        return $this->hasManyThrough(Pole::class, StreetlightTask::class, 'site_id', 'task_id');
    }

    /**
     * Get the project this streetlight site belongs to
     * Relationship: Streetlight belongs to Project
     * Foreign Key: streetlights.project_id → projects.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    // Scope for counting total poles
    public function scopeTotalPoles($query, $projectId)
    {
        return $query->where('project_id', $projectId)->sum('total_poles');
    }

    // Scope for counting surveyed poles
    public function scopeSurveyDone($query, $projectId)
    {
        return $query->where('project_id', $projectId)
            ->sum('number_of_surveyed_poles');
    }

    // Scope for counting installed poles
    public function scopeInstallationDone($query, $projectId)
    {
        return $query->where('project_id', $projectId)
            ->sum('number_of_installed_poles');
    }

    /**
     * Get all streetlight tasks for this site
     * Relationship: Streetlight has many StreetlightTasks
     * Foreign Key: streetlight_tasks.site_id → streetlights.id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function streetlightTasks()
    {
        return $this->hasMany(StreetlightTask::class, 'site_id');
    }
}

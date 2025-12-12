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

    // Relationship: A streetlight has multiple poles
    public function poles()
    {
        return $this->hasMany(Pole::class);
    }


    // Define the relationship
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

    // Relationship with engineer
    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
    // In the Streetlight model
    public function streetlightTasks()
    {
        return $this->hasMany(StreetlightTask::class, 'site_id');
    }

    public function tasks()
    {
        return $this->hasMany(StreetlightTask::class, 'site_id');
        // Modify the method which should contain only task per site id
    }
    public function task()
    {
        // Assumes your 'streetlight_poles' table has a 'task_id' foreign key.
        return $this->belongsTo(StreetlightTask::class, 'task_id');
    }
}

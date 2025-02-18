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
        'pole',
        'complete_pole_number',
        'uname',
        'SID',
        'district_id',
        'block_id',
        'panchayat_id',
        'ward_id',
        'pole_id',
        'luminary_qr',
        'battery_qr',
        'panel_qr',
        'file',
        'lat',
        'lng',
        'beneficiary',
        'remark',
        'project_id'
    ];
    // Define the relationship
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    // Scope for counting total poles
    public function scopeTotalPoles($query, $projectId)
    {
        return $query->where('project_id', $projectId)->sum('pole');
    }

    // Scope for counting surveyed poles
    public function scopeSurveyDone($query, $projectId)
    {
        return $query->where('project_id', $projectId)
            ->where('isSurveyDone', true)
            ->sum('pole');
    }

    // Scope for counting installed poles
    public function scopeInstallationDone($query, $projectId)
    {
        return $query->where('project_id', $projectId)
            ->where('isInstallationDone', true)
            ->sum('pole');
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    use HasFactory;
    protected $table = 'streelight_poles';
    protected $fillable = [
        'task_id',
        'isSurveyDone',
        'isNetworkAvailable',
        'isInstallationDone',
        'luminary_qr',
        'battery_qr',
        'panel_qr',
        'complete_pole_number',
        'beneficiary',
        'remarks',
        'ward_name',
        'beneficiary_contact',
        'sim_number',
        'submission_image',
        'survey_image',
        'lat',
        'lng',
        'file'
    ];
    protected $casts = [
        'survey_image' => 'array',
    ];

    // Relationship: A pole belongs to a streetlight
    public function streetlight()
    {
        return $this->belongsTo(Streetlight::class);
    }
    public function task()
    {
        return $this->belongsTo(StreetlightTask::class, 'task_id');
    }
}

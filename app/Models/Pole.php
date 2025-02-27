<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    use HasFactory;
    protected $fillable = [
        'streetlight_id',
        'isSurveyDone',
        'isNetworkAvailable',
        'isInstallationDone',
        'luminary_qr',
        'battery_qr',
        'panel_qr',
        'complete_pole_number',
        'lat',
        'lng',
        'file'
    ];
    // Relationship: A pole belongs to a streetlight
    public function streetlight()
    {
        return $this->belongsTo(Streetlight::class);
    }

    // Relationship: A pole has many tasks
    public function tasks()
    {
        return $this->hasMany(StreetlightTask::class, 'pole_id');
    }
}

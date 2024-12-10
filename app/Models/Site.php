<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'site_name',
        'state',
        'district',
        'location',
        'project_capacity',
        'ca_number',
        'contact_no',
        'ic_vendor_name',
        'sanction_load',
        'meter_number',
        'load_enhancement_status',
        'site_survey_status',
        'net_meter_sr_no',
        'solar_meter_sr_no',
        'material_inspection_date',
        'spp_installation_date',
        'commissioning_date',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

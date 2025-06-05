<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $fillable = [
        'breda_sl_no',
        'project_id', //FIXME:Project id wala comented tha fillable that's why ye null ja raha tha
        'site_name',
        'state',
        'district',
        'division',
        'installation_status',
        'bts_department_name',
        'location',
        'project_capacity',
        'ca_number',
        'shadow_free_area',
        'division',
        'block',
        'department_name',
        'sanction_load',
        'contact_no',
        'ic_vendor_name',
        'meter_number',
        'load_enhancement_status',
        'site_survey_status',
        'net_meter_sr_no',
        'category',
        'solar_meter_sr_no',
        'material_inspection_date',
        'spp_installation_date',
        'commissioning_date',
        'remarks',
        'site_engineer',
        'survey_latitude',
        'survey_longitude',
        'actual_latitude',
        'actual_longitude'
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

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    public function districtRelation()
    {
        return $this->belongsTo(City::class, 'district');
    }

    public function projectRelation()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function vendorRelation()
    {
        return $this->belongsTo(User::class, 'ic_vendor_name');
    }

    public function engineerRelation()
    {
        return $this->belongsTo(User::class, 'site_engineer');
    }
}

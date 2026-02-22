<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Physical project site for rooftop projects. Contains location details (state, city, pincode),
 * site type, and associated poles. Represents where rooftop solar installations happen.
 *
 * Data Flow:
 *   Import from Excel / Manual create → Assign to task → Field work at site → Poles
 *   installed → Mark complete
 *
 * @depends-on Project, Pole, Task
 * @business-domain Site Management
 * @package App\Models
 */
class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'breda_sl_no',
        'project_id',
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
        'actual_longitude',

        // Newly added enum fields
        'drawing_approval',
        'inspection',
        'material_supplied',
        'structure_installation',
        'structure_foundation',
        'pv_module_installation',
        'inverter_installation',
        'dcdb_acdb_installaation',
        'dc_cabelling',
        'ac_cabelling',
        'ac_cable_termination',
        'dc_earthing',
        'ac_earthing',
        'lighntning_arrestor',
        'remote_monitoring_unit',
        'fire_safety',
        'net_meter_registration',
        'meter_installaton_commission',
        'performance_guarantee_test',
        'handover_status',
    ];

    /**
     * Project.
     *
     * @return void  
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Inventory.
     *
     * @return void  
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Tasks.
     *
     * @return void  
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * State relation.
     *
     * @return void  
     */
    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    /**
     * District relation.
     *
     * @return void  
     */
    public function districtRelation()
    {
        return $this->belongsTo(City::class, 'district');
    }

    /**
     * Project relation.
     *
     * @return void  
     */
    public function projectRelation()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Vendor relation.
     *
     * @return void  
     */
    public function vendorRelation()
    {
        return $this->belongsTo(User::class, 'ic_vendor_name');
    }

    /**
     * Engineer relation.
     *
     * @return void  
     */
    public function engineerRelation()
    {
        return $this->belongsTo(User::class, 'site_engineer');
    }
}

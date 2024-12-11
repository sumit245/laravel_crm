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
  'site_engineer',
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
  return $this->belongsTo(State::class, 'state');
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

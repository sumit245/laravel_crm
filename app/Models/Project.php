<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
 use HasFactory;

 protected $fillable = ['project_name', 'project_in_state', 'start_date', 'work_order_number', 'rate', 'project_capacity', 'end_date', 'description', 'total'];

 public function sites()
 {
  return $this->hasMany(Site::class);
 }
 public function tasks()
 {
  return $this->hasMany(Task::class);
 }

 public function stores()
 {
  return $this->hasMany(Stores::class);
 }
 public function stateRelation()
 {
  return $this->belongsTo(State::class, 'state');
 }

 public function districtRelation()
 {
  return $this->belongsTo(City::class, 'district');
 }
}

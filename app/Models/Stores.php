<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
 use HasFactory;
 protected $fillable = [
  'store_name', 'address', 'project_id', 'store_incharge_id',
 ];

 public function user()
 {
  return $this->belongsTo(User::class, 'storeIncharge', 'id');
 }
 public function inventory()
 {
  return $this->hasMany(Inventory::class);
 }

}

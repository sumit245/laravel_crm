<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inventory item record for rooftop projects. Tracks product name, brand, serial number,
 * quantity, unit, and received date. Quantity is decremented when dispatched to vendors.
 *
 * Data Flow:
 *   GRN import / Manual add → Stored in warehouse → Dispatched to vendor → Quantity
 *   decremented → Consumed at site
 *
 * @depends-on Stores, Project
 * @business-domain Inventory & Warehouse
 * @package App\Models
 */
class Inventory extends Model
{
 use HasFactory;
 protected $table    = "inventory";
 protected $fillable = [
  'store_id',
  'category',
  'sub_category',
  'productName',
  'brand',
  'description',
  'unit',
  'initialQuantity',
  'rate',
  'total',
  'quantityStock',
  'materialDispatchDate',
  'deliveryDate',
  'receivedDate',
  'allocationOfficer',
  'url',
  'project_id',
  'site_id',
 ];

 /**
  * Site.
  *
  * @return void  
  */
 public function site()
 {
  return $this->belongsTo(Site::class);
 }

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
  * Store a newly created resource in storage.
  *
  * @return void  
  */
 public function store()
 {
  return $this->belongsTo(Stores::class);
 }
}

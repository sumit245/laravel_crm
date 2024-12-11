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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table = "inventory";
    protected $fillable = [
        'productName',
        'brand',
        'description',
        'unit',
        'initialQuantity',
        'quantityStock',
        'materialDispatchDate',
        'deliveryDate',
        'receivedDate',
        'allocationOfficer',
        'url',
        'project_id',
        'site_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventroyStreetLightModel extends Model
{
    use HasFactory;
    protected $table = 'inventory_streetlight';
    // Define the fillable attributes
    protected $fillable = [
        'project_id',       // Foreign key for project
        'store_id',         // Foreign key for store
        'item_code',        // Item code
        'item',             // Item name
        'manufacturer',      // Manufacturer name
        'make',             // Make of the item
        'model',            // Model of the item
        'serial_number',    // Serial number of the item
        'hsn',              // HSN code
        'unit',             // Unit of measurement
        'rate',             // Unit rate
        'quantity',         // Quantity of items
        'total_value',      // Total value of the items
        'description',      // Description of the item
        'eway_bill',        // E-way bill number
        'received_date',    // Date the items were received
    ];
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function store()
    {
        return $this->belongsTo(Stores::class);
    }
    public function dispatch(){
        return $this->hasOne(InventoryDispatch::class, 'serial_number', 'serial_number');     
    }
}

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
        'sim_number',       // SIM number (for luminary items only - SL02)
        'hsn',              // HSN code
        'unit',             // Unit of measurement
        'rate',             // Unit rate
        'quantity',         // Quantity of items
        'total_value',      // Total value of the items
        'description',      // Description of the item
        'eway_bill',        // E-way bill number
        'received_date',    // Date the items were received
    ];
    /**
     * Get the site this inventory belongs to (if applicable)
     * Relationship: InventoryStreetlight belongs to Site
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the project this inventory belongs to
     * Relationship: InventoryStreetlight belongs to Project
     * Foreign Key: inventory_streetlight.project_id → projects.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the store this inventory belongs to
     * Relationship: InventoryStreetlight belongs to Store
     * Foreign Key: inventory_streetlight.store_id → stores.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Stores::class);
    }

    /**
     * Get the dispatch record for this inventory item
     * Relationship: InventoryStreetlight has one InventoryDispatch
     * Foreign Key: inventory_dispatch.serial_number → inventory_streetlight.serial_number
     * Note: Relationship is via serial_number (not a foreign key constraint in database)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dispatch(){
        return $this->hasOne(InventoryDispatch::class, 'serial_number', 'serial_number');     
    }
}

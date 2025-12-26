<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDispatch extends Model
{
    use HasFactory;
    protected $table = 'inventory_dispatch';
    protected $fillable = [
        'vendor_id',
        'project_id',
        'store_id',
        'store_incharge_id',
        'item_code',
        'item',
        'rate',
        'make',
        'model',
        'total_quantity',
        'total_value',
        'serial_number',
        'isDispatched',
        'dispatch_date',
        'is_consumed',
        'streetlight_pole_id'

    ];

    /**
     * Get the inventory item (rooftop projects)
     * Relationship: InventoryDispatch belongs to Inventory
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the inventory streetlight item this dispatch is based on
     * Relationship: InventoryDispatch belongs to InventoryStreetlight
     * Foreign Key: inventory_dispatch.serial_number → inventory_streetlight.serial_number
     * Note: Relationship is via serial_number (not a foreign key constraint in database)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventoryStreetLight()
    {
        return $this->belongsTo(InventroyStreetLightModel::class, 'serial_number', 'serial_number');
    }

    /**
     * Get the vendor who received this dispatch
     * Relationship: InventoryDispatch belongs to User (Vendor)
     * Foreign Key: inventory_dispatch.vendor_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project this dispatch belongs to
     * Relationship: InventoryDispatch belongs to Project
     * Foreign Key: inventory_dispatch.project_id → projects.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the store this dispatch came from
     * Relationship: InventoryDispatch belongs to Store
     * Foreign Key: inventory_dispatch.store_id → stores.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Stores::class);
    }

    /**
     * Get the store incharge who authorized this dispatch
     * Relationship: InventoryDispatch belongs to User (Store Incharge)
     * Foreign Key: inventory_dispatch.store_incharge_id → users.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storeIncharge()
    {
        return $this->belongsTo(User::class, 'store_incharge_id');
    }

    /**
     * Get the pole where this inventory was consumed
     * Relationship: InventoryDispatch belongs to Pole
     * Foreign Key: inventory_dispatch.streetlight_pole_id → streelight_poles.id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function streetlightPole()
    {
        return $this->belongsTo(Pole::class, 'streetlight_pole_id', 'id');
    }
}

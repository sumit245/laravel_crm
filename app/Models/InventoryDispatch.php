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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function inventoryStreetLight()
    {
        return $this->belongsTo(InventroyStreetLightModel::class, 'inventory_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function store()
    {
        return $this->belongsTo(Stores::class);
    }
    public function storeIncharge()
    {
        return $this->belongsTo(User::class, 'store_incharge_id');
    }
    public function streetlightPole()
    {
        return $this->belongsTo(Pole::class, 'id');
    }
}

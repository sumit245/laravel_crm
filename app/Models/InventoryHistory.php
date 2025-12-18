<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryHistory extends Model
{
    use HasFactory;

    protected $table = 'inventory_history';

    protected $fillable = [
        'inventory_id',
        'inventory_type',
        'action',
        'user_id',
        'project_id',
        'store_id',
        'quantity_before',
        'quantity_after',
        'serial_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with this history entry
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the store associated with this history entry
     */
    public function store()
    {
        return $this->belongsTo(Stores::class);
    }

    /**
     * Get the inventory item (polymorphic based on inventory_type)
     */
    public function inventory()
    {
        if ($this->inventory_type === 'rooftop') {
            return $this->belongsTo(Inventory::class, 'inventory_id');
        } elseif ($this->inventory_type === 'streetlight') {
            return $this->belongsTo(InventroyStreetLightModel::class, 'inventory_id');
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Physical warehouse/store location associated with a project. Each store has a name, address,
 * and an assigned store incharge (responsible person). All inventory for a project is tracked
 * under its store.
 *
 * Data Flow:
 *   Admin creates store → Assign incharge → Import inventory to store → Track stock
 *   levels → Dispatch from store
 *
 * @depends-on Project, User
 * @business-domain Inventory & Warehouse
 * @package App\Models
 */
class Stores extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_name',
        'address',
        'project_id',
        'store_incharge_id',
    ];

    /**
     * User.
     *
     * @return void  
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'store_incharge_id', 'id');
    }

    /**
     * Store incharge in the database.
     *
     * @return void  
     */
    public function storeIncharge()
    {
        return $this->belongsTo(User::class, 'store_incharge_id', 'id');
    }

    /**
     * Project.
     *
     * @return void  
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Inventory.
     *
     * @return void  
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

}

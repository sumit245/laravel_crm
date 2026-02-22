<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle type master data for travel allowance calculation. Contains vehicle name (Bike, Car,
 * Bus, Auto, etc.) and per-kilometer fare rate used to calculate conveyance amounts.
 *
 * Data Flow:
 *   Admin defines vehicle types with rates → Staff selects vehicle during travel → Rate
 *   used to calculate fare
 *
 * @business-domain Finance & Expense
 * @package App\Models
 */
class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_name',
        'category',
        'sub_category',
        'icon',
        'rate',
    ];

    /**
     * Conveyances.
     *
     * @return void  
     */
    public function conveyances()
    {
        return $this->hasMany(Conveyance::class);
    }

    public $timestamps = true;
}

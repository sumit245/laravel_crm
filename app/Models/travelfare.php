<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Travel fare rate master data for TA/DA. Defines per-kilometer rates for each vehicle type and
 * staff category. Used to calculate the travel component of conveyance claims.
 *
 * Data Flow:
 *   Admin sets per-km rates → Staff selects vehicle + enters distance → System calculates:
 *   distance × rate → Travel allowance amount
 *
 * @depends-on Vehicle, UserCategory
 * @business-domain Finance & Expense
 * @package App\Models
 */
class travelfare extends Model
{
    use HasFactory;
    protected $fillable = [
        'from',
        'to',
        'departure_date',
        'departure_time',
        'arrival_date',
        'arrival_time',
        'modeoftravel',
        'add_total_km',
        'add_rate_per_km',
        'add_rent',
        'add_vehicle_no',
        'amount',
        'tada_id',
    ];

    /**
     * Tada.
     *
     * @return void  
     */
    public function tada(){
        return $this->belongsTo(Tada::class);
    }

}

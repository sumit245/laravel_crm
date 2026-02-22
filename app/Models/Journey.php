<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Individual travel journey leg within a conveyance claim. Contains departure, destination,
 * distance, vehicle type, and calculated fare. Multiple journeys make up a single day's travel
 * claim.
 *
 * Data Flow:
 *   Staff records journey → Fare calculated (distance × vehicle rate) → Multiple journeys
 *   aggregated into daily conveyance → Admin reviews
 *
 * @depends-on Conveyance, Vehicle
 * @business-domain Finance & Expense
 * @package App\Models
 */
class Journey extends Model
{
    use HasFactory;

    protected $table = 'journies';
    protected $fillable = [
        'tickets_provided_by_company',
        'tada_id',
        'from',
        'to',
        'pnr',
        'ticket',
        'mode_of_transport',
        'date_of_journey',
        'amount',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily fare rate master data for TA/DA. Defines the daily allowance (dearness allowance) amount
 * based on staff category and city tier. Used to calculate the per-day DA component of travel
 * claims.
 *
 * Data Flow:
 *   Admin sets rates per category + city tier → Staff submits claim → System looks up
 *   applicable rate → Calculates daily allowance
 *
 * @depends-on UserCategory, City
 * @business-domain Finance & Expense
 * @package App\Models
 */
class dailyfare extends Model
{
    use HasFactory;

    protected $fillable = [
        'place',
        'HotelBillNo',
        'date_of_stay',
        'amount',
        'tada_id'
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

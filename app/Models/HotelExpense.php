<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Hotel/accommodation expense record for field staff travel. Captures hotel name,
 * check-in/check-out dates, amount, and receipt. Part of the TA/DA reimbursement system.
 *
 * Data Flow:
 *   Staff submits hotel bill → Record created → Admin reviews → Approve/Reject →
 *   Include in TA/DA summary
 *
 * @depends-on User, Tada
 * @business-domain Finance & Expense
 * @package App\Models
 */
class HotelExpense extends Model
{
    use HasFactory;
    protected $table = 'hotelexpenses';
    protected $fillable = [
        'guest_house_available',
        'tada_id',
        'certificate_by_district_incharge',
        'check_in_date',
        'check_out_date',
        'breakfast_included',
        'hotel_bill',
        'hotel_bill_no',
        'other_charges',
        'amount',
        'dining_cost',
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

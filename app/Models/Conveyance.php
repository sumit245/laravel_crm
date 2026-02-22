<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily travel conveyance claim submitted by field staff. Contains journey details, vehicle used,
 * distance, fare amount, and approval status (Pending/Accepted/Rejected).
 *
 * Data Flow:
 *   Field staff submits from app → Admin reviews → Accept/Reject → Aggregate in TADA
 *   report → Disburse allowance
 *
 * @depends-on User, Vehicle, Tada
 * @business-domain Finance & Expense
 * @package App\Models
 */
class Conveyance extends Model
{
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'kilometer',
        'amount',
        'created_at',
        'date',
        'image',
        'time',
        'status',
        'vehicle_category',
        'user_id', // add this
    ];

    /**
     * Relationship to user
     *
     * @return void  
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to vehicle
     *
     * @return void  
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_category');
    }
}

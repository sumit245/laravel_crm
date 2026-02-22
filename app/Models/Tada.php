<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Travel Allowance / Dearness Allowance (TA/DA) summary record. Aggregates daily conveyance
 * claims into periodic summaries for disbursement. Contains total amount, distance, and approval
 * status.
 *
 * Data Flow:
 *   Daily conveyances aggregated → Create TADA summary → Admin approval → Disbursement
 *   → Financial reporting
 *
 * @depends-on User, Conveyance, Project
 * @business-domain Finance & Expense
 * @package App\Models
 */
class Tada extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'visit_approve',
        'status',
        'visiting_to',
        'purpose_of_visit',
        'outcome_achieved',
        'date_of_departure',
        'date_of_return',
        'miscellaneous',   
        'amount'
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
     * Journey.
     *
     * @return void  
     */
    public function journey(){
        return $this->hasMany(Journey::class);
    }

    /**
     * Hotel expense.
     *
     * @return void  
     */
    public function hotelExpense(){
        return $this->hasMany(HotelExpense::class);
    }

    /**
     * Usercategory.
     *
     * @return void  
     */
    public function usercategory(){
        return $this->belongsTo(UserCategory::class);
    }

}

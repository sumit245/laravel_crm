<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * City master data for TA/DA calculations. Each city has a category (A/B/C tier) that determines
 * daily allowance rates for field staff. Linked to a state for geographic hierarchy.
 *
 * Data Flow:
 *   Admin defines cities with categories → Staff selects city during travel → Category
 *   determines daily allowance rate
 *
 * @depends-on State
 * @business-domain Finance & Expense
 * @package App\Models
 */
class City extends Model
{
    use HasFactory;

    /**
     * Write code on Method
     *
     * @return response()
     */

     protected $fillable = [
        'name', 
        'state_id',
        'tier',
        'category',
        'user_category_id',
        'room_min_price',
        'room_max_price'
    ];

    /**
     * Usercategory.
     *
     * @return void  
     */
    public function usercategory(){
        return $this->belongsTo(UserCategory::class, 'user_category_id', 'id');
    }

}

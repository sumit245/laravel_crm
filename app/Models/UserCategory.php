<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Staff category for TA/DA rate determination. Maps users to expense categories (e.g., senior
 * engineer, junior engineer) which determine their daily allowance and per-km travel rates.
 *
 * Data Flow:
 *   Admin assigns category to staff → Category determines allowance rates → Rates applied
 *   to conveyance calculations
 *
 * @depends-on User
 * @business-domain Finance & Expense
 * @package App\Models
 */
class UserCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'name',
        'description',
        'allowed_vehicles',
        'room_min_price',
        'room_max_price',
    ];

    public $timestamps = true;
    
    /**
     * Cities.
     *
     * @return void  
     */
    public function cities(){
        return $this->hasMany(City::class);
    }

    /**
     * Vehicle.
     *
     * @return void  
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'allowed_vehicles', 'vehicle_id');
    }
    /**
     * Users.
     *
     * @return void  
     */
    public function users()
    {
        return $this->hasMany(User::class, 'category', 'id');
    }
}

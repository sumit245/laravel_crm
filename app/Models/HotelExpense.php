<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'amount',
        'dining_cost',
    ];

    public function tada(){
        return $this->belongsTo(Tada::class);
    }

}

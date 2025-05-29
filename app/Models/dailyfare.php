<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function tada(){
        return $this->belongsTo(Tada::class);
    }

}

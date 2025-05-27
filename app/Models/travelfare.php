<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function tada(){
        return $this->belongsTo(Tada::class);
    }

}

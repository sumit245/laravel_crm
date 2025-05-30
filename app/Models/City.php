<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'room_min_price',
        'room_max_price'
    ];
}

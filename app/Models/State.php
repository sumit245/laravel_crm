<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

     /**
     * Write code on Method
     *
     * @return response()
     */

     protected $fillable = [
        'name', 'country_id'
    ];
}

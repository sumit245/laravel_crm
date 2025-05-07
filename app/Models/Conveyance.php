<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conveyance extends Model
{
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'kilometer',
        'created_at',
        'time',
        'status',
        'vehicle_category',
        'user_id', // add this
    ];
    
    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to vehicle
    public function vehicle(){
        return $this->belongsTo(Vehicle::class, 'id', 'category');
    }


}

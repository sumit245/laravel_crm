<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'journey_id',
        'hotel_id',
        
    ];

    

    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journey(){
        return $this->hasMany(Journey::class);
    }

    public function hotel(){
        return $this->hasMany(HotelExpense::class);
    }


}

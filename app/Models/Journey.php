<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journey extends Model
{
    use HasFactory;

    protected $table = 'journies';
    protected $fillable = [
        'tickets_provided_by_company',
        'tada_id',
        'from',
        'to',
        'ticket',
        'mode_of_transport',
        'date_of_journey',
        'amount',
    ];

    public function tada(){
        return $this->belongsTo(Tada::class);
    }

}

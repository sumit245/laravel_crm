<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'name',
        'description',
        'allowed_vehicles',
    ];

    public $timestamps = true;

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'allowed_vehicles', 'vehicle_id');
    }

}

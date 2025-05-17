<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tada extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'name',
        // 'department',
        // 'employee_id',
        'user_id',
        'visit_approve',
        'objective_tour',
        'meeting_visit',
        'outcome_achieve',
        // 'Desgination', // Consider correcting to 'designation' for consistency
        'start_journey',
        'start_journey_time',
        'end_journey',
        'end_journey_time',
        'transport',
        'start_journey_pnr',
        'from_city',
        'to_city',
        'end_journey_pnr',
        'total_km',
        'rate_per_km',
        'Rent',
        'vehicle_no',
        'category',
        'description_category'
    ];

    protected $casts = [
        'start_journey_pnr' => 'array',
        'end_journey_pnr' => 'array',
        'start_journey' => 'date',
        'end_journey' => 'date',
        'other'
    ];

    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

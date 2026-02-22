<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Log entry for each attempt to push pole data to the government RMS system. Records request
 * payload, response, status, and error details for debugging integration issues.
 *
 * Data Flow:
 *   Push attempted → Log request/response → Track success/failure → Admin reviews push
 *   history
 *
 * @depends-on Pole
 * @business-domain Government Integration
 * @package App\Models
 */
class RmsPushLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pole_id',
        'message',
        'response_data',
        'district',
        'block',
        'panchayat',
        'pushed_by',
        'pushed_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'pushed_at' => 'datetime',
    ];

    /**
     * Pole.
     *
     * @return void  
     */
    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    /**
     * Pushed by.
     *
     * @return void  
     */
    public function pushedBy()
    {
        return $this->belongsTo(User::class, 'pushed_by');
    }
}

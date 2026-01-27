<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function pushedBy()
    {
        return $this->belongsTo(User::class, 'pushed_by');
    }
}

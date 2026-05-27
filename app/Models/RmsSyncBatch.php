<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmsSyncBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'scope',
        'requested_by',
        'status',
        'total_poles',
        'processed_poles',
        'success_count',
        'error_count',
        'started_at',
        'finished_at',
        'last_error',
    ];

    protected $casts = [
        'scope' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}


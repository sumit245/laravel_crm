<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmsSetting extends Model
{
    use HasFactory;

    protected $fillable = ['endpoint', 'auth_mode', 'enabled', 'timeout_seconds', 'retry_count'];

    protected $casts = [
        'enabled' => 'boolean',
        'timeout_seconds' => 'integer',
        'retry_count' => 'integer',
    ];
}

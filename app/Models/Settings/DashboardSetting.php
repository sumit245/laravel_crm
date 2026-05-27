<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    use HasFactory;

    protected $fillable = ['default_date_filter', 'default_project_behavior', 'visible_widgets'];

    protected $casts = [
        'visible_widgets' => 'array',
    ];
}

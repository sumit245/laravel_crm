<?php

namespace App\Models\Settings;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorEarningSetting extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'pole_rate', 'is_active'];

    protected $casts = [
        'pole_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models\Settings;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoleNumberFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'ward_type',
        'name',
        'tokens',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'tokens' => 'array',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

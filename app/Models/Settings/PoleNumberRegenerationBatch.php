<?php

namespace App\Models\Settings;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoleNumberRegenerationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pole_number_format_id',
        'project_id',
        'ward_type',
        'status',
        'affected_count',
        'processed_count',
        'error_message',
        'created_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function format()
    {
        return $this->belongsTo(PoleNumberFormat::class, 'pole_number_format_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

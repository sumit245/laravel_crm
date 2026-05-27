<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreetlightSiteWard extends Model
{
    use HasFactory;

    public const TYPE_NORMAL = 'normal';
    public const TYPE_GP = 'gp';

    protected $fillable = [
        'streetlight_id',
        'ward_type',
        'ward_number',
        'planned_poles',
        'source',
    ];

    protected $casts = [
        'planned_poles' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Streetlight::class, 'streetlight_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(StreetlightTask::class, 'streetlight_task_wards')
            ->withTimestamps();
    }

    public function getKeyStringAttribute(): string
    {
        return "{$this->ward_type}:{$this->ward_number}";
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->ward_type === self::TYPE_GP
            ? "GP Ward {$this->ward_number}"
            : "Ward {$this->ward_number}";
    }
}

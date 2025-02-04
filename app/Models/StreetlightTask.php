<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreetlightTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'site_id',
        'engineer_id',
        'vendor_id',
        'activity',
        'status',
        'start_date',
        'end_date',
        'materials_consumed',
        'description',
        'approved_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function site()
    {
        return $this->belongsTo(Streetlight::class, 'site_id');
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}

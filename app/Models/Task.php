<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Work assignment for rooftop solar projects. Similar concept to StreetlightTask but for rooftop
 * installations. Contains site assignment, staff assignment, status tracking, and materials
 * consumed.
 *
 * Data Flow:
 *   Admin assigns site to engineer + vendor → Create task → Field work → Update status
 *   → Mark complete
 *
 * @depends-on Site, User, Project
 * @business-domain Field Operations
 * @package App\Models
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'site_id',
        'vendor_id',
        'engineer_id',
        'manager_id',
        'activity',
        'task_name',
        'status',
        'start_date',
        'end_date',
        'image',
        'materials_consumed',
        'description',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'materials_consumed' => 'array',
    ];

    /**
     * Project.
     *
     * @return void  
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Site.
     *
     * @return void  
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Vendor.
     *
     * @return void  
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    /**
     * Engineer.
     *
     * @return void  
     */
    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    /**
     * Manager.
     *
     * @return void  
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}

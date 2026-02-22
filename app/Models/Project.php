<?php

namespace App\Models;

use App\Enums\ProjectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a solar energy installation project (government contract). Each project has a type
 * (1=Streetlight, 2=Rooftop), assigned staff, sites/panchayats, inventory stores, and targets.
 * The project is the top-level organizational entity.
 *
 * Data Flow:
 *   Admin creates → Assign staff → Import sites → Create targets → Track inventory →
 *   Monitor progress → Generate reports
 *
 * @depends-on User, Site, Streetlight, Stores, Task, StreetlightTask
 * @business-domain Core Domain
 * @package App\Models
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_type',
        'agreement_number',
        'agreement_date',
        'project_name',
        'project_in_state',
        'start_date',
        'work_order_number',
        'rate',
        'project_capacity',
        'end_date',
        'description',
        'total'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'agreement_date' => 'date',
    ];

    /**
     * Sites.
     *
     * @return void  
     */
    public function sites()
    {
        return $this->hasMany(Site::class);
    }
    /**
     * Tasks.
     *
     * @return void  
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Streetlight tasks.
     *
     * @return void  
     */
    public function streetlightTasks()
    {
        return $this->hasMany(StreetlightTask::class);
    }

    /**
     * Stores in the database.
     *
     * @return void  
     */
    public function stores()
    {
        return $this->hasMany(Stores::class);
    }
    /**
     * State relation.
     *
     * @return void  
     */
    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state');
    }

    /**
     * District relation.
     *
     * @return void  
     */
    public function districtRelation()
    {
        return $this->belongsTo(City::class, 'district');
    }
    /**
     * Define the inverse relationship
     *
     * @return void  
     */
    public function streetlights()
    {
        return $this->hasMany(Streetlight::class);
    }
    /**
     * Users.
     *
     * @return void  
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')->withPivot('role')->withTimestamps();
    }
}

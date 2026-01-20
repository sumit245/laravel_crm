<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'firstName',
        'lastName',
        'password',
        'contactNo',
        'address',
        'role',
        'category',
        'department',
        'project_id',
        'accountName',
        'accountNumber',
        'ifsc',
        'bankName',
        'branch',
        'gstNumber',
        'pan',
        'aadharNumber',
        'status',
        'disableLogin',
        'lastOnline',
        'project_id',
        'manager_id',
        'vertical_head_id',
        'site_engineer_id',
        'image'
    ];

    // Relationship: Project Manager has many Site Engineers
    public function siteEngineers()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    // Relationship: Site Engineer belongs to a Project Manager
    public function projectManager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Relationship: Site Engineer has many Vendors
    public function vendors()
    {
        return $this->hasMany(User::class, 'site_engineer_id');
    }

    // Relationship: Vendor belongs to a Site Engineer
    public function siteEngineer()
    {
        return $this->belongsTo(User::class, 'site_engineer_id');
    }

    // Relationship: User belongs to a Vertical Head
    public function verticalHead()
    {
        return $this->belongsTo(User::class, 'vertical_head_id');
    }

    // Relationship: Vertical Head has many users
    public function verticalHeadUsers()
    {
        return $this->hasMany(User::class, 'vertical_head_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role', 'district_id')
            ->withTimestamps();
    }
    public function usercategory()
    {
        return $this->belongsTo(UserCategory::class, 'category');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        $firstName = trim($this->attributes['firstName'] ?? '');
        $lastName = trim($this->attributes['lastName'] ?? '');
        $name = trim($firstName . ' ' . $lastName);

        // If both firstName and lastName are empty, check if there's a 'name' column value
        if (empty($name) && isset($this->attributes['name'])) {
            return trim($this->attributes['name']);
        }

        return !empty($name) ? $name : 'Unknown User';
    }

    /**
     * Get the user's role name.
     *
     * @return string
     */
    public function getRoleNameAttribute()
    {
        return UserRole::tryFrom($this->role)?->label() ?? 'Unknown';
    }

    public function meetings()
    {
        return $this->belongsToMany(Meet::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(DiscussionPoints::class, 'assignee_id');
    }

    // Task relationships for performance tracking
    public function managerTasks()
    {
        return $this->hasMany(Task::class, 'manager_id');
    }

    public function engineerTasks()
    {
        return $this->hasMany(Task::class, 'engineer_id');
    }

    public function vendorTasks()
    {
        return $this->hasMany(Task::class, 'vendor_id');
    }

    // Streetlight task relationships
    public function streetlightTasks()
    {
        return $this->hasMany(\App\Models\StreetlightTask::class, 'manager_id');
    }

    public function streetlightEngineerTasks()
    {
        return $this->hasMany(\App\Models\StreetlightTask::class, 'engineer_id');
    }

    public function streetlightVendorTasks()
    {
        return $this->hasMany(\App\Models\StreetlightTask::class, 'vendor_id');
    }

    /**
     * Get all assigned projects for this user (from pivot table)
     */
    public function getAssignedProjects()
    {
        return $this->projects()->get();
    }

    /**
     * Assign user to a project (syncs pivot table)
     *
     * @param int $projectId
     * @param int|null $districtId Optional district context for this user-project assignment
     */
    public function assignToProject($projectId, ?int $districtId = null)
    {
        $projectId = (int) $projectId;

        $pivotAttributes = [];
        if (!is_null($districtId)) {
            $pivotAttributes['district_id'] = $districtId;
        }

        if (!empty($pivotAttributes)) {
            $this->projects()->syncWithoutDetaching([
                $projectId => $pivotAttributes,
            ]);
        } else {
            $this->projects()->syncWithoutDetaching([$projectId]);
        }
        
        // Update primary project_id if not set
        if (!$this->project_id) {
            $this->update(['project_id' => $projectId]);
        }
    }

    /**
     * Remove user from a project
     */
    public function removeFromProject($projectId)
    {
        $this->projects()->detach($projectId);
        
        // If this was the primary project, set primary to first remaining project
        if ($this->project_id == $projectId) {
            $remainingProjects = $this->projects()->first();
            $this->update(['project_id' => $remainingProjects ? $remainingProjects->id : null]);
        }
    }

    /**
     * Replace all project assignments with new ones
     */
    public function replaceProjects(array $projectIds)
    {
        $this->projects()->sync($projectIds);
        
        // Set primary project_id to first project
        $this->update(['project_id' => !empty($projectIds) ? $projectIds[0] : null]);
    }
}

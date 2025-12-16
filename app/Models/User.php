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
        return $this->belongsToMany(Project::class, 'project_user')->withPivot('role')->withTimestamps();
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
        'password'          => 'hashed',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        $name = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
        return !empty($name) ? $name : ($this->name ?? '');
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
}

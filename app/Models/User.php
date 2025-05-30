<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')->withPivot('role')->withTimestamps();
    }
    public function usercategory(){
        return $this->belongsTo(UserCategory::class, 'category', 'id');
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
}

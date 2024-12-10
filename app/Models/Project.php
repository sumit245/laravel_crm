<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['project_name', 'start_date', 'work_order_number', 'rate'];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

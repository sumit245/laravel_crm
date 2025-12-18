<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_name',
        'address',
        'project_id',
        'store_incharge_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'store_incharge_id', 'id');
    }

    public function storeIncharge()
    {
        return $this->belongsTo(User::class, 'store_incharge_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

}

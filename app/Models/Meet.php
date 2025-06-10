<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meet extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'agenda',
        'meet_link',
        'platform',
        'meet_date',
        'meet_time',
        'type',
        'user_ids',
        'notes',
    ];

    protected $casts = [
        'user_ids' => 'array', // Automatically casts JSON to array
        'meet_date' => 'date',
        'meet_time' => 'datetime:H:i',
    ];

    public function participants()
    {
        return User::whereIn('id', $this->user_ids)->get();
    }
}
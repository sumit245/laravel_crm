<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'date_of_offer',
        'address',
        'designation',
        'department',
        'location',
        'doj',
        'ctc',
        'experience',
        'last_salary',
        'document_path',
        'status'
    ];
}

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
        'status',
        // New fields
        'gender',
        'marital_status',
        'nationality',
        'language',
        'permanent_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'education',
        'previous_employer',
        'notice_period',
        'disabilities',
        'currently_employed',
        'reason_for_leaving',
        'other_info',
        'photo_name',
        'photo_s3_path',
        'document_paths',
        'signature'
    ];
    
    // Add casting for JSON fields
    protected $casts = [
        'education' => 'array',
        'document_path' => 'array',
        'document_paths' => 'array',
    ];
}
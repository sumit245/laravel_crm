<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportExportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key',
        'allowed_file_types',
        'max_file_size_kb',
        'sample_format_path',
    ];

    protected $casts = [
        'allowed_file_types' => 'array',
        'max_file_size_kb' => 'integer',
    ];
}

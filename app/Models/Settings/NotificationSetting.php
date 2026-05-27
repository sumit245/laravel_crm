<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'enabled',
        'recipient_roles',
        'database_enabled',
        'mail_enabled',
        'whatsapp_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recipient_roles' => 'array',
        'database_enabled' => 'boolean',
        'mail_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];
}

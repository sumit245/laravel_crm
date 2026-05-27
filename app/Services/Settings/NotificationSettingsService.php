<?php

namespace App\Services\Settings;

use App\Models\Settings\NotificationSetting;

class NotificationSettingsService
{
    public function __construct(private SettingsAuditService $audit)
    {
    }

    public function all()
    {
        return NotificationSetting::orderBy('event_key')->get();
    }

    public function update(array $settings, ?int $userId): void
    {
        $before = $this->all()->keyBy('event_key')->toArray();

        foreach ($settings as $eventKey => $data) {
            NotificationSetting::updateOrCreate(
                ['event_key' => $eventKey],
                [
                    'enabled' => !empty($data['enabled']),
                    'recipient_roles' => array_values($data['recipient_roles'] ?? []),
                    'database_enabled' => !empty($data['database_enabled']),
                    'mail_enabled' => !empty($data['mail_enabled']),
                    'whatsapp_enabled' => !empty($data['whatsapp_enabled']),
                ]
            );
        }

        $this->audit->log('notifications', 'events', $before, $this->all()->keyBy('event_key')->toArray(), $userId);
    }
}

<?php

namespace App\Services\Settings;

use App\Models\Settings\RmsSetting;
use App\Repositories\Settings\SettingsRepository;

class RmsSettingsService
{
    public function __construct(
        private SettingsRepository $settings,
        private SettingsAuditService $audit
    ) {
    }

    public function get(): RmsSetting
    {
        return $this->settings->firstOrCreate(RmsSetting::class, [], [
            'enabled' => false,
            'auth_mode' => 'none',
            'timeout_seconds' => 30,
            'retry_count' => 3,
        ]);
    }

    public function update(array $data, ?int $userId): RmsSetting
    {
        $setting = $this->get();
        $before = $setting->toArray();
        $setting->fill($data);
        $setting->enabled = !empty($data['enabled']);
        $setting->save();
        $this->audit->log('rms', null, $before, $setting->fresh()->toArray(), $userId);

        return $setting;
    }
}

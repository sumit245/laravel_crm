<?php

namespace App\Services\Settings;

use App\Models\Settings\VendorEarningSetting;
use App\Repositories\Settings\SettingsRepository;

class VendorEarningsSettingsService
{
    public function __construct(
        private SettingsRepository $settings,
        private SettingsAuditService $audit
    ) {
    }

    public function global(): VendorEarningSetting
    {
        return $this->settings->firstOrCreate(VendorEarningSetting::class, ['project_id' => null], [
            'pole_rate' => 500,
            'is_active' => true,
        ]);
    }

    public function rateForProject(?int $projectId): float
    {
        if ($projectId) {
            $projectSetting = VendorEarningSetting::where('project_id', $projectId)
                ->where('is_active', true)
                ->first();

            if ($projectSetting) {
                return (float) $projectSetting->pole_rate;
            }
        }

        return (float) $this->global()->pole_rate;
    }

    public function updateGlobal(array $data, ?int $userId): VendorEarningSetting
    {
        $setting = $this->global();
        $before = $setting->toArray();
        $setting->fill([
            'pole_rate' => $data['pole_rate'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        $setting->save();
        $this->audit->log('vendor_earnings', 'global', $before, $setting->fresh()->toArray(), $userId);

        return $setting;
    }

    public function updateProject(array $data, ?int $userId): VendorEarningSetting
    {
        $setting = VendorEarningSetting::firstOrNew(['project_id' => $data['project_id']]);
        $before = $setting->exists ? $setting->toArray() : [];
        $setting->fill([
            'pole_rate' => $data['pole_rate'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        $setting->save();
        $this->audit->log('vendor_earnings', (string) $setting->project_id, $before, $setting->fresh()->toArray(), $userId);

        return $setting;
    }
}

<?php

namespace App\Services\Settings;

use App\Models\Settings\DashboardSetting;
use App\Repositories\Settings\SettingsRepository;

class DashboardSettingsService
{
    public const WIDGETS = ['performance', 'meetings', 'tada', 'top_performers', 'travel_breakdown', 'inventory'];

    public function __construct(
        private SettingsRepository $settings,
        private SettingsAuditService $audit
    ) {
    }

    public function get(): DashboardSetting
    {
        return $this->settings->firstOrCreate(DashboardSetting::class, [], [
            'default_date_filter' => 'this_month',
            'default_project_behavior' => 'first_assigned',
            'visible_widgets' => ['performance', 'meetings', 'tada', 'top_performers'],
        ]);
    }

    public function update(array $data, ?int $userId): DashboardSetting
    {
        $setting = $this->get();
        $before = $setting->toArray();
        $setting->fill([
            'default_date_filter' => $data['default_date_filter'],
            'default_project_behavior' => $data['default_project_behavior'],
            'visible_widgets' => array_values($data['visible_widgets'] ?? []),
        ]);
        $setting->save();
        $this->audit->log('dashboard', null, $before, $setting->fresh()->toArray(), $userId);

        return $setting;
    }
}

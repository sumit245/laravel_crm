<?php

namespace App\Services\Settings;

use App\Models\Settings\ImportExportSetting;

class ImportExportSettingsService
{
    public function __construct(private SettingsAuditService $audit)
    {
    }

    public function all()
    {
        return ImportExportSetting::orderBy('module_key')->get();
    }

    public function update(array $settings, ?int $userId): void
    {
        $before = $this->all()->keyBy('module_key')->toArray();

        foreach ($settings as $moduleKey => $data) {
            ImportExportSetting::updateOrCreate(
                ['module_key' => $moduleKey],
                [
                    'allowed_file_types' => array_values($data['allowed_file_types'] ?? []),
                    'max_file_size_kb' => (int) ($data['max_file_size_kb'] ?? 5120),
                    'sample_format_path' => $data['sample_format_path'] ?? null,
                ]
            );
        }

        $this->audit->log('import_export', 'modules', $before, $this->all()->keyBy('module_key')->toArray(), $userId);
    }
}

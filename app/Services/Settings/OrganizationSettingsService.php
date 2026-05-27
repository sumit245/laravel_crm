<?php

namespace App\Services\Settings;

use App\Models\Settings\OrganizationSetting;
use App\Repositories\Settings\SettingsRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizationSettingsService
{
    public function __construct(
        private SettingsRepository $settings,
        private SettingsAuditService $audit
    ) {
    }

    public function get(): OrganizationSetting
    {
        return $this->settings->firstOrCreate(OrganizationSetting::class);
    }

    public function update(array $data, ?UploadedFile $logo, ?UploadedFile $favicon, ?int $userId): OrganizationSetting
    {
        $setting = $this->get();
        $before = $this->audit->snapshot($setting);

        if ($logo) {
            $this->deletePublicAsset($setting->logo_path);
            $data['logo_path'] = $this->storeImage($logo, 'logo');
        }

        if ($favicon) {
            $this->deletePublicAsset($setting->favicon_path);
            $data['favicon_path'] = $this->storeImage($favicon, 'favicon');
        }

        $setting->fill($data);
        $setting->save();

        $this->audit->log('organization', null, $before, $this->audit->snapshot($setting), $userId);

        return $setting;
    }

    private function storeImage(UploadedFile $file, string $prefix): string
    {
        $filename = $prefix . '_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('settings/branding', $filename, 'public');
    }

    private function deletePublicAsset(?string $stored): void
    {
        $relativePath = $this->publicDiskRelativePath($stored);
        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }

    private function publicDiskRelativePath(?string $stored): string
    {
        if (! $stored) {
            return '';
        }

        if (str_contains($stored, '/storage/')) {
            return ltrim(Str::after($stored, '/storage/'), '/');
        }

        if (! str_contains($stored, '://')) {
            return ltrim($stored, '/');
        }

        return '';
    }
}

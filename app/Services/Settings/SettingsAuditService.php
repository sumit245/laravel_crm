<?php

namespace App\Services\Settings;

use App\Models\Settings\SettingsChangeLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SettingsAuditService
{
    public function log(string $group, ?string $key, array $oldValue, array $newValue, ?int $userId): void
    {
        if ($this->normalize($oldValue) === $this->normalize($newValue)) {
            return;
        }

        SettingsChangeLog::create([
            'setting_group' => $group,
            'setting_key' => $key,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId,
            'changed_at' => now(),
        ]);
    }

    public function snapshot(Model $model): array
    {
        return Arr::except($model->getAttributes(), ['created_at', 'updated_at']);
    }

    private function normalize(array $value): array
    {
        ksort($value);
        return $value;
    }
}

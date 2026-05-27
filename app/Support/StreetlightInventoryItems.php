<?php

namespace App\Support;

class StreetlightInventoryItems
{
    public const CATEGORY_PANEL = 'panel';
    public const CATEGORY_LUMINARY = 'luminary';
    public const CATEGORY_BATTERY = 'battery';
    public const CATEGORY_STRUCTURE = 'structure';
    public const CATEGORY_OTHER = 'other';

    public static function normalizeCode(mixed $code): ?string
    {
        if ($code === null || trim((string) $code) === '') {
            return null;
        }

        return strtoupper(trim((string) $code));
    }

    public static function category(?string $itemName, ?string $itemCode = null): string
    {
        $name = strtolower(trim((string) $itemName));
        $legacyCode = strtoupper(trim((string) $itemCode));

        if (str_contains($name, 'luminary') || str_contains($name, 'luminaire') || $legacyCode === 'SL02') {
            return self::CATEGORY_LUMINARY;
        }

        if (str_contains($name, 'battery') || $legacyCode === 'SL03') {
            return self::CATEGORY_BATTERY;
        }

        if (str_contains($name, 'structure') || $legacyCode === 'SL04') {
            return self::CATEGORY_STRUCTURE;
        }

        if (str_contains($name, 'module') || str_contains($name, 'panel') || $legacyCode === 'SL01') {
            return self::CATEGORY_PANEL;
        }

        return self::CATEGORY_OTHER;
    }

    public static function isLuminary(?string $itemName, ?string $itemCode = null): bool
    {
        return self::category($itemName, $itemCode) === self::CATEGORY_LUMINARY;
    }

    public static function poleQrColumn(?string $itemName, ?string $itemCode = null): ?string
    {
        return match (self::category($itemName, $itemCode)) {
            self::CATEGORY_PANEL => 'panel_qr',
            self::CATEGORY_LUMINARY => 'luminary_qr',
            self::CATEGORY_BATTERY => 'battery_qr',
            default => null,
        };
    }

    public static function defaultTemplateRows(): array
    {
        return [
            ['PANEL-01', 'Module', 'Example Manufacturer', 'Example Make', 'Panel-120W', 'PANEL-SERIAL-001', '', '85414300', 'Nos', 2094, 1, 2094, 'Solar panel/module', 'EWB-001', now()->format('Y-m-d')],
            ['LUM-01', 'Luminary', 'Example Manufacturer', 'Example Make', 'LED-30W', 'LUM-SERIAL-001', 'SIM-001', '94054090', 'Nos', 1200, 1, 1200, 'Luminary with SIM', 'EWB-001', now()->format('Y-m-d')],
            ['BAT-01', 'Battery', 'Example Manufacturer', 'Example Make', 'BAT-12V', 'BAT-SERIAL-001', '', '85076000', 'Nos', 1800, 1, 1800, 'Battery', 'EWB-001', now()->format('Y-m-d')],
            ['STRUCT-01', 'Structure', 'Example Manufacturer', 'Example Make', 'Pole Structure', 'STRUCT-SERIAL-001', '', '73089090', 'Nos', 900, 1, 900, 'Mounting structure', 'EWB-001', now()->format('Y-m-d')],
        ];
    }
}

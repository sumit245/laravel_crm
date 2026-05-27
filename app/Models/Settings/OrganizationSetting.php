<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrganizationSetting extends Model
{
    use HasFactory;

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    protected $fillable = [
        'company_name',
        'legal_name',
        'gst_number',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'favicon_path',
    ];

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => self::publicAssetUrl($this->logo_path));
    }

    protected function faviconUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => self::publicAssetUrl($this->favicon_path));
    }

    /**
     * Browser URL for a file on the public disk. Uses a root-relative path so the
     * logo works on 127.0.0.1, localhost, or production regardless of APP_URL.
     */
    public static function publicAssetUrl(?string $stored): ?string
    {
        if (blank($stored)) {
            return null;
        }

        if (str_contains($stored, '/storage/')) {
            return '/storage/'.ltrim(Str::after($stored, '/storage/'), '/');
        }

        if (! str_contains($stored, '://')) {
            return '/storage/'.ltrim($stored, '/');
        }

        return $stored;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key, with default fallback and caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Alias for get() to prevent method collision or mismatch.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::get($key, $default);
    }

    /**
     * Set/update a setting value and clear its cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget("system_setting_{$key}");

        return $setting;
    }

    /**
     * Check if a boolean setting is truthy.
     */
    public static function isTrue(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default ? '1' : '0');
        return in_array(strtolower((string) $val), ['1', 'true', 'yes', 'on'], true);
    }
}

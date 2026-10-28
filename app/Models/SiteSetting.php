<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/** Small key / value settings. Used for the two logos (shown in light and in dark mode). */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (!Schema::hasTable('site_settings')) {
            return $default;
        }
        $all = Cache::remember('site_settings', 300, fn () => self::pluck('value', 'key')->all());

        return ($all[$key] ?? null) ?: $default;
    }

    public static function put(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings');
    }

    /** Logo for a mode: the uploaded one, else the other mode's, else the built in file. $mode = light | dark. */
    public static function logo(string $mode): string
    {
        $mine = self::get('logo_' . $mode);
        $other = self::get('logo_' . ($mode === 'light' ? 'dark' : 'light'));
        $path = $mine ?: $other;

        return $path ? url(Storage::url('files/' . $path)) : url($mode === 'light' ? 'images/logo.png' : 'images/logo-2.png');
    }
}

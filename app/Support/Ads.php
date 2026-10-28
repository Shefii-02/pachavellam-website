<?php

namespace App\Support;

use App\Models\AdUnit;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Google ads, managed in admin/ads. Switches: master, apps (AdMob), website (AdSense); then each ad type, and the pages it shows on.
 * In a paged list a unit shows after every N items of a page, at most `max_per_page` times, from page `start_page` on.
 */
class Ads
{
    public static function platformOn(string $platform): bool
    {
        return SiteSetting::get('ads_master', '0') === '1' && SiteSetting::get($platform === 'web' ? 'ads_web' : 'ads_app', '0') === '1';
    }

    /** All units of a platform (cached for a minute). */
    public static function units(string $platform)
    {
        if (!Schema::hasTable('ad_units')) {
            return collect();
        }

        return Cache::remember('ad_units:' . $platform, 60, fn () => AdUnit::where('platform', $platform)->get());
    }

    public static function forget(): void
    {
        Cache::forget('ad_units:web');
        Cache::forget('ad_units:app');
    }

    /** Does this person see no ads of this kind because they bought a course? */
    public static function hiddenForUser(AdUnit $u): bool
    {
        if (!$u->hide_for_paid || !auth()->check()) {
            return false;
        }
        static $paid = null;
        $paid ??= auth()->user()->activeAdmissions()->isNotEmpty();

        return $paid;
    }

    /** A website unit to show on a page (banner / article ad), or null. */
    public static function web(string $type, string $page): ?AdUnit
    {
        if (!self::platformOn('web')) {
            return null;
        }
        $u = self::units('web')->firstWhere('type', $type);

        return $u && $u->onPage($page) && !self::hiddenForUser($u) ? $u : null;
    }

    /** An in-feed unit after item number $pos (1 based) of page $pageNo of a list, or null. */
    public static function inList(string $type, string $page, int $pos, int $pageNo = 1): ?AdUnit
    {
        $u = self::web($type, $page);
        if (!$u || $u->every_n < 4 || $pos < 4 || $pageNo < max(1, $u->start_page) || $pos % $u->every_n !== 0) {
            return null;
        }

        return intdiv($pos, $u->every_n) <= max(1, $u->max_per_page) ? $u : null;
    }
}

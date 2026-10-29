<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// exception pages are saved one HTML file per error and never cleaned up; keep two weeks
Illuminate\Support\Facades\Schedule::call(function () {
    foreach (glob(storage_path('logs/exceptions/*.html')) ?: [] as $path) {
        if (filemtime($path) < now()->subDays(14)->timestamp) {
            @unlink($path);
        }
    }
})->daily()->name('prune-exception-pages');

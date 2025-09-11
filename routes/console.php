<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly sync last 30 days at 02:30 server time
Schedule::command('facebook:sync-with-video-metrics --last30 --limit=10000')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Comprehensive Facebook data sync every 6 hours
Schedule::command('facebook:sync-all-data --days=7 --limit=50')
    ->everySixHours()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

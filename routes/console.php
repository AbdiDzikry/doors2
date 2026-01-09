<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sync:employees')->dailyAt('02:00');

// Schedule Room Status Update (Every Minute)
// This ensures tablets update when a meeting starts/ends without polling
Schedule::command('app:update-room-status')->everyMinute();

// Schedule Auto-Cancel Unattended Meetings (Every 5 Minutes)
Schedule::command('app:cancel-unattended-meetings')->everyFiveMinutes();

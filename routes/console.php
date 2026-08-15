<?php

use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:complete-past', function () {
    $updated = Event::completePastEvents();

    $this->info($updated . ' past event(s) marked as completed.');
})->purpose('Mark past open events as completed');

Schedule::command('events:complete-past')->dailyAt('00:05');

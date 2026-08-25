<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:list', function () {
    $users = User::query()
        ->orderBy('id')
        ->get(['id', 'first_name', 'last_name', 'email', 'username', 'role']);

    $this->table(
        ['ID', 'Name', 'Email', 'Username', 'Role'],
        $users->map(function (User $user) {
            return [
                $user->id,
                $user->fullName(),
                $user->email,
                $user->username,
                $user->role,
            ];
        })->all()
    );
})->purpose('List all users');

Artisan::command('events:complete-past', function () {
    $updated = Event::completePastEvents();

    $this->info($updated . ' past event(s) marked as completed.');
})->purpose('Mark past open events as completed');

Schedule::command('events:complete-past')->dailyAt('00:05');

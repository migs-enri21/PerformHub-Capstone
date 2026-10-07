<?php

use App\Models\Booking;
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

Artisan::command('events:refresh-statuses', function () {
    $updated = Event::refreshStatuses();

    $this->info($updated . ' event status(es) refreshed.');
})->purpose('Refresh open and ended event statuses');

Artisan::command('bookings:expire-past', function () {
    $expired = Booking::expirePastUnconfirmed();

    $this->info($expired.' unconfirmed booking(s) expired.');
})->purpose('Expire unconfirmed bookings after their event ends');

Schedule::command('events:refresh-statuses')->everyMinute();
Schedule::command('bookings:expire-past')->everyMinute();

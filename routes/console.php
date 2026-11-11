<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

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

    $this->info($updated.' event status(es) refreshed.');
})->purpose('Refresh open and ended event statuses');

Artisan::command('bookings:expire-past', function () {
    $expired = Booking::expirePastUnconfirmed();
    $finished = Booking::finishPastBooked();

    $this->info($expired.' unconfirmed booking(s) expired.');
    $this->info($finished.' booked event(s) marked completed.');
})->purpose('Expire unconfirmed bookings and mark finished booked events');

Artisan::command('registration-drafts:prune', function () {
    $disk = Storage::disk('local');
    $cutoff = now()->subDays(7)->getTimestamp();
    $deleted = 0;

    foreach ($disk->directories('registration-drafts') as $directory) {
        if ($disk->lastModified($directory) < $cutoff) {
            $disk->deleteDirectory($directory);
            $deleted++;
        }
    }

    $this->info($deleted.' expired registration draft(s) removed.');
})->purpose('Remove temporary files from abandoned registrations');

Schedule::command('events:refresh-statuses')->everyMinute();
Schedule::command('bookings:expire-past')->everyMinute();
Schedule::command('registration-drafts:prune')->daily();

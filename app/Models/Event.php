<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'organizer_id',
        'event_type_id',
        'title',
        'description',
        'event_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'preferred_genres',
        'budget',
        'compensation_type',
        'first_prize',
        'second_prize',
        'third_prize',
        'rate_per_hour',
        'work_hours_per_day',
        'status',
        'cover_photo',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'preferred_genres' => 'array',
        'event_date' => 'date',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function eventType()
    {
         return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category');
    }

    public function applications()
    {
        return $this->hasMany(EventApplication::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->orderBy('sort_order');
    }

    public static function refreshStatuses(): int
    {
        $events = static::whereIn('status', ['Open', 'open'])->get();
        $updated = 0;

        foreach ($events as $event) {
            $status = 'Open';

            if ($event->hasEnded()) {
                $status = 'Ended';
            }

            if (strcasecmp($event->status, $status) !== 0) {
                $event->update(['status' => $status]);
                $updated++;
            }
        }

        return $updated;
    }

    public function hasStarted(): bool
    {
        return now()->greaterThanOrEqualTo($this->startsAt());
    }

    public function hasEnded(): bool
    {
        return now()->greaterThanOrEqualTo($this->endsAt());
    }

    public function startsAt(): Carbon
    {
        return $this->dateTimeOnEventDate($this->start_time);
    }

    public function endsAt(): Carbon
    {
        $endTime = $this->end_time ?: $this->start_time;
        $endDate = $this->end_date instanceof Carbon
            ? $this->end_date->toDateString()
            : ($this->end_date
                ? Carbon::parse($this->end_date)->toDateString()
                : ($this->event_date instanceof Carbon
                    ? $this->event_date->toDateString()
                    : Carbon::parse($this->event_date)->toDateString()));

        $end = Carbon::parse($endDate.' '.$this->normalizeTimeString($endTime));
        $start = $this->startsAt();

        // Legacy overnight rows only — new events must end same day.
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $end;
    }

    private function dateTimeOnEventDate(mixed $time): Carbon
    {
        $date = $this->event_date instanceof Carbon
            ? $this->event_date->toDateString()
            : Carbon::parse($this->event_date)->toDateString();

        $time = $this->normalizeTimeString($time);

        return Carbon::parse($date.' '.$time);
    }

    private function normalizeTimeString(mixed $time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i:s');
        }

        $time = trim((string) $time);

        if ($time === '') {
            return '00:00:00';
        }

        // If a full datetime somehow arrives, keep the time portion only.
        if (str_contains($time, ' ')) {
            $parts = explode(' ', $time);

            return end($parts) ?: '00:00:00';
        }

        return $time;
    }

    public function dateRangeLabel(string $format = 'F j, Y'): string
    {
        $endDate = $this->end_date;

        if ($this->event_date->isSameDay($endDate)) {
            return $this->event_date->format($format);
        }

        return $this->event_date->format($format).' - '.$endDate->format($format);
    }

    public function coverPhotoUrl(): ?string
    {
        if (!$this->cover_photo) {
            return null;
        }

        if (str_starts_with($this->cover_photo, 'http')) {
            return $this->cover_photo;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->cover_photo);
    }
}

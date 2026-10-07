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
        'status',
        'cover_photo',
    ];

    protected $casts = [
        'preferred_genres' => 'array',
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

    public function hasGalleryPhotos(): bool
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->isNotEmpty() || $this->cover_photo !== null;
        }

        return $this->photos()->exists() || $this->cover_photo !== null;
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
        $start = Carbon::parse($this->event_date.' '.$this->start_time);

        return now()->greaterThanOrEqualTo($start);
    }

    public function hasEnded(): bool
    {
        $start = Carbon::parse($this->event_date.' '.$this->start_time);
        $endTime = $this->end_time;

        if (! $endTime) {
            $endTime = $this->start_time;
        }

        $end = Carbon::parse($this->event_date.' '.$endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return now()->greaterThanOrEqualTo($end);
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

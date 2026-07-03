<?php

namespace App\Models;

use App\Models\Concerns\HasPhilippineLocation;
use App\Support\SocialMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformerProfile extends Model
{
    use HasPhilippineLocation;

    protected $fillable = [
        'user_id',
        'stage_name',
        'bio',
        'genre',
        'category_id',
        'rate',
        'location',
        'region',
        'city',
        'barangay',
        'profile_photo',
        'banner_photo',
        'social_facebook',
        'social_facebook_followers',
        'social_instagram',
        'social_instagram_followers',
        'social_youtube',
        'social_youtube_subscribers',
        'social_tiktok',
        'social_tiktok_followers',
        'social_twitter',
        'social_twitter_followers',
        'is_verified_badge',
        'google_calendar_connected',
        'google_calendar_id',
        'google_refresh_token',
        'google_token_expires_at',
        'google_calendar_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_verified_badge' => 'boolean',
            'google_calendar_connected' => 'boolean',
            'google_refresh_token' => 'encrypted',
            'google_token_expires_at' => 'datetime',
            'google_calendar_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function availabilitySchedules(): HasMany
    {
        return $this->hasMany(AvailabilitySchedule::class);
    }

    public function googleCalendarBusyDates(): HasMany
    {
        return $this->hasMany(GoogleCalendarBusyDate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'performer_id', 'user_id');
    }

    public function isAvailableOnDate(string $date): bool
    {
        $hasActiveBooking = $this->bookings()
            ->whereDate('event_date', $date)
            ->whereIn('status', ['pending', 'interview_scheduled', 'accepted', 'completed'])
            ->exists();

        if ($hasActiveBooking) {
            return false;
        }

        if ($this->google_calendar_connected) {
            $hasGoogleBusyDate = $this->googleCalendarBusyDates()
                ->whereDate('date', $date)
                ->exists();

            if ($hasGoogleBusyDate) {
                return false;
            }
        }

        $schedule = $this->availabilitySchedules()
            ->whereDate('date', $date)
            ->first();

        if (! $schedule) {
            return true;
        }

        return (bool) $schedule->is_available;
    }

    public function averageRating(): float
    {
        return (float) Review::query()
            ->where('reviewee_id', $this->user_id)
            ->avg('rating') ?? 0;
    }

    public function socialLinks(): array
    {
        return array_filter([
            'facebook' => $this->social_facebook,
            'instagram' => $this->social_instagram,
            'youtube' => $this->social_youtube,
            'tiktok' => $this->social_tiktok,
            'twitter' => $this->social_twitter,
        ]);
    }

    /** @return array<string, array{url: string, count: ?int, label: string, metric: string, formatted: string}> */
    public function socialStats(): array
    {
        $countFields = [
            'facebook' => 'social_facebook_followers',
            'instagram' => 'social_instagram_followers',
            'youtube' => 'social_youtube_subscribers',
            'tiktok' => 'social_tiktok_followers',
            'twitter' => 'social_twitter_followers',
        ];

        $stats = [];

        foreach (SocialMedia::platformMeta() as $platform => $meta) {
            $url = $this->{"social_{$platform}"};

            if (! $url) {
                continue;
            }

            $count = $this->{$countFields[$platform]};

            $stats[$platform] = [
                'url' => $url,
                'count' => $count,
                'label' => $meta['label'],
                'metric' => $meta['metric'],
                'formatted' => SocialMedia::formatCount($count),
            ];
        }

        return $stats;
    }

    public function displayTags(): array
    {
        return array_values(array_filter(array_unique([
            $this->category?->name,
            $this->genre,
        ])));
    }

    public function shortLocation(): string
    {
        if ($this->city) {
            return $this->city.', Philippines';
        }

        return $this->location ?: 'Philippines';
    }
}

<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
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
        'budget',
        'status',
        'cover_photo',
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

    public static function completePastEvents(): int
    {
        return static::whereIn('status', ['Open', 'open'])
            ->whereDate('event_date', '<', today())
            ->update(['status' => 'Completed']);
    }

    public function coverPhotoUrl(): ?string
    {
        if (! $this->cover_photo) {
            return null;
        }

        if (str_starts_with($this->cover_photo, 'http')) {
            return $this->cover_photo;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->cover_photo);
    }
}

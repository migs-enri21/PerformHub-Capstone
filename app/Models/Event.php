<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use  App\Models\Category;

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
        'preferred_category_id',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function eventType()
    {
         return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function preferredCategory()
    {
        return $this->belongsTo(Category::class, 'preferred_category_id');
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
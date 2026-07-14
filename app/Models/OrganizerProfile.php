<?php

namespace App\Models;

use App\Models\Concerns\HasPhilippineLocation;
use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerProfile extends Model
{
    use HasPhilippineLocation;

    protected $fillable = [
        'user_id',
        'organization_name',
        'organization_type',
        'bio',
        'location',
        'region',
        'city',
        'barangay',
        'profile_photo',
        'phone',
        'website',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->profile_photo);
    }

    public function shortLocation(): string
    {
        if ($this->city || $this->region) {
            $parts = array_filter([$this->city, $this->region, 'Philippines']);
            return implode(', ', $parts);
        }

        return $this->location ?: 'Philippines';
    }
}

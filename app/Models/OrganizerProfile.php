<?php

namespace App\Models;

use App\Models\Concerns\HasPhilippineLocation;
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
}

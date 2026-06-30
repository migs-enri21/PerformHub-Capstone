<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_PERFORMER = 'performer';

    public const ROLE_ORGANIZER = 'organizer';

    public const ROLE_ADMIN = 'admin';

    public const ONBOARDING_REGISTERED = 0;

    public const ONBOARDING_PROFILE = 1;

    public const ONBOARDING_VERIFICATION = 2;

    public const ONBOARDING_COMPLETE = 3;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'is_verified',
        'is_active',
        'onboarding_step',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->fullName();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function performerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PerformerProfile::class);
    }

    public function organizerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrganizerProfile::class);
    }

    public function bookingsAsOrganizer(): HasMany
    {
        return $this->hasMany(Booking::class, 'organizer_id');
    }

    public function bookingsAsPerformer(): HasMany
    {
        return $this->hasMany(Booking::class, 'performer_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function verificationDocuments(): HasMany
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function isPerformer(): bool
    {
        return $this->role === self::ROLE_PERFORMER;
    }

    public function isOrganizer(): bool
    {
        return $this->role === self::ROLE_ORGANIZER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_PERFORMER => route('performer.dashboard'),
            self::ROLE_ORGANIZER => route('organizer.dashboard'),
            self::ROLE_ADMIN => route('admin.dashboard'),
            default => route('home'),
        };
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_step >= self::ONBOARDING_COMPLETE;
    }

    public function hasLimitedAccess(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        return ! $this->hasCompletedOnboarding();
    }

    public function onboardingStepLabel(): string
    {
        return match ($this->onboarding_step) {
            self::ONBOARDING_REGISTERED => 'role',
            self::ONBOARDING_PROFILE => 'profile',
            self::ONBOARDING_VERIFICATION => 'verification',
            default => 'complete',
        };
    }

    public function onboardingRoute(): string
    {
        if ($this->hasCompletedOnboarding()) {
            return $this->dashboardRoute();
        }

        return match ($this->onboarding_step) {
            self::ONBOARDING_REGISTERED => route('onboarding.role'),
            self::ONBOARDING_PROFILE => route('onboarding.profile'),
            self::ONBOARDING_VERIFICATION => route('onboarding.verification'),
            default => route('onboarding.complete'),
        };
    }

    public function avatarUrl(int $size = 128): string
    {
        $photo = null;
        $name = $this->fullName();

        if ($this->isPerformer() && $this->performerProfile) {
            $photo = $this->performerProfile->profile_photo;
            $name = $this->performerProfile->stage_name ?: $name;
        } elseif ($this->isOrganizer() && $this->organizerProfile) {
            $photo = $this->organizerProfile->profile_photo;
            $name = $this->organizerProfile->organization_name ?: $name;
        }

        if ($photo) {
            return asset('storage/'.$photo);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=6346ff&color=fff&size='.$size;
    }

    public function profileRoute(): ?string
    {
        return match ($this->role) {
            self::ROLE_PERFORMER => route('performer.profile.show'),
            self::ROLE_ORGANIZER => route('organizer.profile.edit'),
            default => null,
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'organizer_id',
        'performer_id',
        'event_name',
        'event_date',
        'event_time',
        'venue',
        'requirements',
        'duration_hours',
        'status',
        'contract_path',
        'contract_confirmed_at',
        'performer_confirmed_contract',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'contract_confirmed_at' => 'datetime',
            'performer_confirmed_contract' => 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public function interview(): HasOne
    {
        return $this->hasOne(Interview::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'interview_scheduled' => 'Interview Scheduled',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'interview_scheduled' => 'bg-info',
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            'completed' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}

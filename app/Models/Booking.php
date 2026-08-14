<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'organizer_id',
        'performer_id',
        'event_id',
        'event_name',
        'event_date',
        'event_time',
        'end_time',
        'venue',
        'requirements',
        'duration_hours',
        'status',
        'contract_path',
        'signed_contract_path',
        'signed_contract_uploaded_at',
        'contract_confirmed_at',
        'performer_confirmed_contract',
        'notes',
        'budget',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'signed_contract_uploaded_at' => 'datetime',
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
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
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            'completed' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function hasContract(): bool
    {
        return filled($this->contract_path);
    }

    public function contractUrl(): ?string
    {
        if (! $this->hasContract()) {
            return null;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->contract_path);
    }

    public function hasSignedContract(): bool
    {
        return filled($this->signed_contract_path);
    }

    public function signedContractUrl(): ?string
    {
        if (! $this->hasSignedContract()) {
            return null;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->signed_contract_path);
    }
}

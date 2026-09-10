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
        'source',
        'contract_path',
        'signed_contract_path',
        'signed_contract_uploaded_at',
        'signwell_document_id',
        'signwell_status',
        'signwell_signing_url',
        'signwell_sent_at',
        'signwell_completed_at',
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
            'signwell_sent_at' => 'datetime',
            'signwell_completed_at' => 'datetime',
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
        switch ($this->status) {
            case 'pending':
                return 'Pending';
            case 'accepted':
                return 'Accepted';
            case 'rejected':
                return 'Rejected';
            case 'completed':
                return 'Completed';
            default:
                return ucfirst($this->status);
        }
    }

    public function statusBadgeClass(): string
    {
        switch ($this->status) {
            case 'pending':
                return 'bg-warning text-dark';
            case 'accepted':
                return 'bg-success';
            case 'rejected':
                return 'bg-danger';
            case 'completed':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
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

    public function cameFromApplication(): bool
    {
        return $this->source === 'application';
    }

    public function sameDayConfirmedConflict(): ?self
    {
        return static::query()
            ->where('performer_id', $this->performer_id)
            ->whereDate('event_date', $this->event_date)
            ->whereIn('status', ['accepted', 'completed'])
            ->where('id', '!=', $this->id)
            ->first();
    }
}

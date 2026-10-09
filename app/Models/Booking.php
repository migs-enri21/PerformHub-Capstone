<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Carbon\Carbon;
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
        'cancel_reason',
        'cancel_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'signed_contract_uploaded_at' => 'datetime',
            'cancel_requested_at' => 'datetime',
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
            case 'cancelled':
                return 'Cancelled';
            case 'rejected':
                return 'Rejected';
            case 'completed':
                return 'Booked';
            case 'expired':
                return 'Expired';
            default:
                return ucfirst($this->status);
        }
    }

    public function statusBadgeClass(): string
    {
        switch ($this->status) {
            case 'pending':
                return 'bg-warning text-dark';
            case 'cancelled':
                return 'bg-secondary';
            case 'accepted':
                return 'bg-primary';
            case 'rejected':
                return 'bg-danger';
            case 'completed':
                return 'bg-success';
            case 'expired':
                return 'bg-danger';
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
        if (!$this->hasContract()) {
            return null;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->contract_path);
    }

    public function hasSignedContract(): bool
    {
        return filled($this->signed_contract_path);
    }

    public function isSignWellCompleted(): bool
    {
        $status = strtolower(trim((string) $this->signwell_status));

        return in_array($status, ['completed', 'manually completed'], true);
    }

    public function isSigned(): bool
    {
        return $this->hasSignedContract() || $this->isSignWellCompleted();
    }

    public function eventHasEnded(): bool
    {
        $date = $this->event_date->toDateString();
        $start = Carbon::parse($date.' '.$this->event_time);
        $endTime = $this->end_time;

        if (! $endTime) {
            $endTime = $this->event_time;
        }
        $end = Carbon::parse($date.' '.$endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return now()->greaterThanOrEqualTo($end);
    }

    public static function sweepPastBookings(): void
    {
        static::expirePastUnconfirmed();
    }

    public static function expirePastUnconfirmed(): int
    {
        $bookings = static::query()
            ->with(['organizer', 'performer'])
            ->whereIn('status', ['pending', 'accepted'])
            ->whereDate('event_date', '<=', today())
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            if (! $booking->eventHasEnded()) {
                continue;
            }

            $booking->update([
                'status' => 'expired',
                'cancel_requested_at' => null,
            ]);

            $message = '"'.$booking->event_name.'" expired because the event ended before the booking was confirmed.';

            if ($booking->performer) {
                \App\Models\Notification::send(
                    $booking->performer,
                    'booking',
                    'Booking Expired',
                    $message,
                    route('performer.bookings.show', $booking)
                );
            }

            if ($booking->organizer) {
                \App\Models\Notification::send(
                    $booking->organizer,
                    'booking',
                    'Booking Expired',
                    $message,
                    route('organizer.bookings.show', $booking)
                );
            }

            $count++;
        }

        return $count;
    }

    public function locksTheDate(): bool
    {
        return $this->status === 'completed';
    }

    public function scopeLockingDate($query)
    {
        return $query->where('status', 'completed');
    }

    public function markCompletedFromSignature(): bool
    {
        if ($this->status === 'completed') {
            return true;
        }

        if ($this->status !== 'accepted' || !$this->isSigned()) {
            return false;
        }

        if ($this->sameDayConfirmedConflict()) {
            return false;
        }

        $confirmedAt = $this->contract_confirmed_at;

        if ($confirmedAt === null) {
            $confirmedAt = now();
        }

        $this->update([
            'status' => 'completed',
            'contract_confirmed_at' => $confirmedAt,
            'performer_confirmed_contract' => true,
        ]);

        $this->dropSameDayCompetitors();

        return true;
    }

    public function signedContractUrl(): ?string
    {
        if (!$this->hasSignedContract()) {
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
            ->lockingDate()
            ->where('id', '!=', $this->id)
            ->first();
    }

    public function dropSameDayCompetitors(): void
    {
        $others = static::query()
            ->with('organizer')
            ->where('performer_id', $this->performer_id)
            ->whereDate('event_date', $this->event_date)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('id', '!=', $this->id)
            ->get();

        foreach ($others as $other) {
            $other->update(['status' => 'rejected']);

            if ($other->event_id) {
                \App\Models\EventApplication::where('event_id', $other->event_id)
                    ->where('performer_id', $other->performer_id)
                    ->update(['status' => 'declined']);
            }

            \App\Models\Notification::send(
                $other->organizer,
                'booking',
                'Date no longer available',
                'This date was taken after another booking was signed, so "'.$other->event_name.'" was released.',
                route('organizer.bookings.show', $other)
            );
        }
    }

    public function canRequestCancel(): bool
    {
        if (! in_array($this->status, ['accepted', 'completed'], true) || $this->cancel_requested_at !== null) {
            return false;
        }
    
        return $this->event_date->toDateString() >= now()->toDateString();
    }

    public function hasCancelRequest(): bool
    {
        return in_array($this->status, ['accepted', 'completed'], true) && $this->cancel_requested_at !== null;
    }

    public function scopeCancelRequested($query)
    {
        return $query->whereNotNull('cancel_requested_at')
            ->whereIn('status', ['accepted', 'completed']);
    }
}

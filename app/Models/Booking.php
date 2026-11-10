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
        'end_date',
        'assigned_start_date',
        'assigned_end_date',
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
            'end_date' => 'date',
            'assigned_start_date' => 'date',
            'assigned_end_date' => 'date',
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
            case 'finished':
                return 'Completed';
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
                return 'ph-badge ph-badge-action';
            case 'cancelled':
                return 'ph-badge ph-badge-muted';
            case 'accepted':
                return 'ph-badge ph-badge-accepted';
            case 'rejected':
                return 'ph-badge ph-badge-rejected';
            case 'completed':
                return 'ph-badge ph-badge-success';
            case 'finished':
                return 'ph-badge ph-badge-finished';
            case 'expired':
                return 'ph-badge ph-badge-expired';
            default:
                return 'ph-badge ph-badge-muted';
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

    public function resolvedEndDate(): Carbon
    {
        $endDate = $this->end_date ?: $this->event_date;

        if ($endDate === null) {
            return $this->resolvedEventDate();
        }

        if ($endDate instanceof Carbon) {
            return $endDate->copy()->startOfDay();
        }

        return Carbon::parse($endDate)->startOfDay();
    }

    public function resolvedEventDate(): Carbon
    {
        if ($this->event_date instanceof Carbon) {
            return $this->event_date->copy()->startOfDay();
        }

        if ($this->event_date === null) {
            return now()->startOfDay();
        }

        return Carbon::parse($this->event_date)->startOfDay();
    }

    public function isSingleDayEvent(): bool
    {
        return $this->resolvedEventDate()->isSameDay($this->resolvedEndDate());
    }

    public function eventHasEnded(): bool
    {
        $startDate = $this->assignmentStartDate();
        $endDate = $this->assignmentEndDate();

        $startTime = $this->normalizeTimeString($this->event_time);
        $endTime = $this->normalizeTimeString($this->end_time ?: $this->event_time);

        $start = Carbon::parse($startDate->toDateString().' '.$startTime);
        $end = Carbon::parse($endDate->toDateString().' '.$endTime);

        // Legacy overnight rows that only stored one date.
        if ($endDate->isSameDay($startDate) && $end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return now()->greaterThanOrEqualTo($end);
    }

    private function normalizeTimeString(mixed $time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i:s');
        }

        $time = trim((string) $time);

        if ($time === '') {
            return '00:00:00';
        }

        if (str_contains($time, ' ')) {
            $parts = explode(' ', $time);

            return end($parts) ?: '00:00:00';
        }

        return $time;
    }

    public function eventHasStarted(): bool
    {
        $startDate = $this->assignmentStartDate();
        $startTime = $this->normalizeTimeString($this->event_time);
        $start = Carbon::parse($startDate->toDateString().' '.$startTime);

        return now()->greaterThanOrEqualTo($start);
    }

    public function dateRangeLabel(string $format = 'F j, Y'): string
    {
        $startDate = $this->resolvedEventDate();
        $endDate = $this->resolvedEndDate();

        if ($startDate->isSameDay($endDate)) {
            return $startDate->format($format);
        }

        return $startDate->format($format).' - '.$endDate->format($format);
    }

    public function assignmentStartDate(): Carbon
    {
        if ($this->assigned_start_date instanceof Carbon) {
            return $this->assigned_start_date->copy()->startOfDay();
        }

        if ($this->assigned_start_date) {
            return Carbon::parse($this->assigned_start_date)->startOfDay();
        }

        return $this->resolvedEventDate();
    }

    public function assignmentEndDate(): Carbon
    {
        if ($this->assigned_end_date instanceof Carbon) {
            return $this->assigned_end_date->copy()->startOfDay();
        }

        if ($this->assigned_end_date) {
            return Carbon::parse($this->assigned_end_date)->startOfDay();
        }

        return $this->resolvedEndDate();
    }

    public function assignmentDateRangeLabel(string $format = 'F j, Y'): string
    {
        $startDate = $this->assignmentStartDate();
        $endDate = $this->assignmentEndDate();

        if ($startDate->isSameDay($endDate)) {
            return $startDate->format($format);
        }

        return $startDate->format($format).' - '.$endDate->format($format);
    }

    public function coversDate($date): bool
    {
        $checkedDate = Carbon::parse($date)->startOfDay();

        return $checkedDate->betweenIncluded($this->resolvedEventDate(), $this->resolvedEndDate());
    }

    public function assignmentCoversDate($date): bool
    {
        $checkedDate = Carbon::parse($date)->startOfDay();

        return $checkedDate->betweenIncluded($this->assignmentStartDate(), $this->assignmentEndDate());
    }

    public static function sweepPastBookings(): void
    {
        static::expirePastUnconfirmed();
        static::finishPastBooked();
    }

    public static function expirePastUnconfirmed(): int
    {
        $bookings = static::query()
            ->with(['organizer', 'performer'])
            ->whereIn('status', ['pending', 'accepted'])
            ->whereDate('end_date', '<=', today())
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

    public static function finishPastBooked(): int
    {
        $bookings = static::query()
            ->where('status', 'completed')
            ->whereDate('assigned_end_date', '<=', today())
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            if (! $booking->eventHasEnded()) {
                continue;
            }

            $booking->update([
                'status' => 'finished',
                'cancel_requested_at' => null,
            ]);

            $count++;
        }

        return $count;
    }

    public function locksTheDate(): bool
    {
        return in_array($this->status, ['completed', 'finished'], true);
    }

    public function scopeLockingDate($query)
    {
        return $query->whereIn('status', ['completed', 'finished']);
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
        $startDate = $this->assignmentStartDate();
        $endDate = $this->assignmentEndDate();

        return static::query()
            ->where('performer_id', $this->performer_id)
            ->overlappingAssignmentDates($startDate, $endDate)
            ->lockingDate()
            ->where('id', '!=', $this->id)
            ->first();
    }

    public function dropSameDayCompetitors(): void
    {
        $startDate = $this->assignmentStartDate();
        $endDate = $this->assignmentEndDate();

        $others = static::query()
            ->with('organizer')
            ->where('performer_id', $this->performer_id)
            ->overlappingAssignmentDates($startDate, $endDate)
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
    
        return ! $this->eventHasEnded();
    }

    public function scopeOverlappingDates($query, $startDate, $endDate)
    {
        return $query
            ->whereDate('event_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate);
    }

    public function scopeOverlappingAssignmentDates($query, $startDate, $endDate)
    {
        return $query
            ->whereDate('assigned_start_date', '<=', $endDate)
            ->whereDate('assigned_end_date', '>=', $startDate);
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

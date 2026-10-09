@extends('layouts.app')

@section('title', 'New Booking')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $eventDetails = [
        'id' => null,
        'budget' => null,
    ];

    if ($selectedEvent) {
        $eventDetails = [
            'id' => $selectedEvent->id,
            'budget' => $selectedEvent->budget,
        ];
    }

    $selectedEventBudget = $eventDetails['budget'];
    $fromApplicationValue = 0;

    if (!empty($fromApplication)) {
        $fromApplicationValue = 1;
    }
@endphp

<h2 class="fw-bold mb-2">
    @if(!empty($fromApplication))
        Accept Application: {{ $performer->stage_name }}
    @else
        Book {{ $performer->stage_name }}
    @endif
</h2>

@if(!empty($fromApplication))
    <p class="text-muted mb-4">This performer already applied. Submitting this form marks the application as accepted.</p>
@endif
@if($existingBooking)
    <div class="alert alert-success">
        @if($existingBooking->status === 'pending')
            A booking request has already been sent to this performer for this event.
        @else
            This performer is already booked for this event.
        @endif
        <a href="{{ route('organizer.bookings.show', $existingBooking) }}" class="alert-link ms-2">View Booking</a>
    </div>
@else
<form method="POST" action="{{ route('organizer.bookings.store', $performer) }}" class="organizer-booking-form">
    @csrf
    <input type="hidden" name="from_application" value="{{ $fromApplicationValue }}">
    <div class="ph-card p-4">
        <div class="row g-3">
            <div class="mb-4">

    <label class="form-label fw-semibold" for="eventSelector">Choose an Active Event</label>

    <select id="eventSelector" class="form-select ph-input">

        <option value="">Select an active event</option>

        @foreach($events as $event)
            @php
                $eventTypeName = 'Event type not set';
                $compensationLabel = 'Compensation not set';

                if ($event->eventType) {
                    $eventTypeName = $event->eventType->name;
                }

                if ($event->compensation_type === 'fixed') {
                    $compensationLabel = 'Fixed Budget';
                }

                if ($event->compensation_type === 'hourly') {
                    $compensationLabel = 'Rate per Hour';
                }

                if ($event->compensation_type === 'contest') {
                    $compensationLabel = 'Contest Prizes';
                }

                $eventMediaUrl = '';
                $eventMediaType = '';

                if ($event->photos->isNotEmpty()) {
                    $firstMedia = $event->photos->first();
                    $eventMediaUrl = $firstMedia->fileUrl();

                    if ($firstMedia->isVideo()) {
                        $eventMediaType = 'video';
                    } else {
                        $eventMediaType = 'image';
                    }
                } elseif ($event->coverPhotoUrl()) {
                    $eventMediaUrl = $event->coverPhotoUrl();
                    $eventMediaType = 'image';
                }
            @endphp

            <option
                value="{{ $event->id }}"
                data-id="{{ $event->id }}"
                data-title="{{ $event->title }}"
                data-date="{{ $event->event_date }}"
                data-start="{{ $event->start_time }}"
                data-end="{{ $event->end_time }}"
                data-venue="{{ $event->venue }}"
                data-description="{{ $event->description }}"
                data-budget="{{ $event->budget }}"
                data-rate-per-hour="{{ $event->rate_per_hour }}"
                data-compensation-type="{{ $event->compensation_type }}"
                data-first-prize="{{ $event->first_prize }}"
                data-second-prize="{{ $event->second_prize }}"
                data-third-prize="{{ $event->third_prize }}"
                data-date-label="{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M d, Y') }}"
                data-time-label="{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}"
                data-event-type="{{ $eventTypeName }}"
                data-compensation="{{ $compensationLabel }}"
                data-media-url="{{ $eventMediaUrl }}"
                data-media-type="{{ $eventMediaType }}"
                @selected($eventDetails['id'] == $event->id)>

                {{ $event->title }} — {{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M d') }}, {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}

            </option>
        @endforeach

            </select>
            <small class="text-muted d-block mt-2">Only open events that have not started are shown.</small>
            <input type="hidden" name="event_id" id="event_id" value="{{ $eventDetails['id'] }}">
            @error('event_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div id="selectedEventSummary" class="organizer-booking-event-summary d-none">
                <div class="organizer-booking-event-summary-content">
                    <div class="organizer-booking-event-media d-none" id="summaryEventMedia">
                        <img id="summaryEventImage" src="" alt="Selected event media">
                        <div class="organizer-booking-event-video-preview d-none" id="summaryEventVideoPreview">
                            <i class="fas fa-play" aria-hidden="true"></i>
                            <span>Video</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="organizer-booking-event-summary-header">
                            <div>
                                <div class="organizer-booking-event-summary-title">Selected Event</div>
                                <div class="organizer-booking-event-summary-name" id="summaryEventName"></div>
                            </div>
                            <div class="organizer-booking-event-summary-badges">
                                <span class="organizer-booking-event-badge" id="summaryEventType"></span>
                                <span class="organizer-booking-event-badge organizer-booking-event-badge--compensation" id="summaryEventCompensation"></span>
                            </div>
                        </div>
                        <div class="organizer-booking-event-summary-details">
                            <span><i class="fas fa-calendar-alt" aria-hidden="true"></i><span id="summaryEventDate"></span></span>
                            <span><i class="far fa-clock" aria-hidden="true"></i><span id="summaryEventTime"></span></span>
                            <span><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span id="summaryEventVenue"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-none" id="contestPrizeNotice">
                <div class="organizer-contest-prize-notice" role="note">
                    <div class="organizer-contest-prize-notice-icon"><i class="fas fa-trophy" aria-hidden="true"></i></div>
                    <div>
                        <strong>Prize-based contest entry</strong>
                        <p>Contestants do not receive a guaranteed booking fee. Only the winners receive prizes.</p>
                        <div class="organizer-contest-prize-details" id="contestPrizeDetails"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4"><label class="form-label text-muted small" id="budgetOfferLabel">Budget Offer (₱)</label><input type="number" name="budget" id="budget" class="form-control ph-input @error('budget') is-invalid @enderror" value="{{ old('budget', $selectedEventBudget) }}" step="0.01"><small class="text-muted d-none" id="hourlyRateHelp"></small>@error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12"><label class="form-label text-muted small">Notes for Performer (Optional)</label><textarea name="notes" class="form-control ph-input" rows="2">{{ old('notes') }}</textarea></div>

        </div>
        <button type="submit" class="btn ph-btn-primary mt-4">
            @if(!empty($fromApplication))
                Accept Application
            @else
                Send Booking Request
            @endif
        </button>
    </div>
</form>
@endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {

    const selector = document.getElementById('eventSelector');
    const selectedEventSummary = document.getElementById('selectedEventSummary');
    const budgetInput = document.getElementById('budget');
    const budgetOfferField = budgetInput.closest('.col-md-4');
    const budgetOfferLabel = document.getElementById('budgetOfferLabel');
    const hourlyRateHelp = document.getElementById('hourlyRateHelp');
    const contestPrizeNotice = document.getElementById('contestPrizeNotice');
    const contestPrizeDetails = document.getElementById('contestPrizeDetails');
    const summaryEventMedia = document.getElementById('summaryEventMedia');
    const summaryEventImage = document.getElementById('summaryEventImage');
    const summaryEventVideoPreview = document.getElementById('summaryEventVideoPreview');

    function updateSelectedEvent() {

            const selected = selector.options[selector.selectedIndex];

            if (!selected.dataset.id) {
                document.getElementById('event_id').value = '';
                selectedEventSummary.classList.add('d-none');
                budgetInput.value = '';
                budgetOfferLabel.textContent = 'Budget Offer (₱)';
                hourlyRateHelp.classList.add('d-none');
                contestPrizeNotice.classList.add('d-none');
                summaryEventMedia.classList.add('d-none');
                return;
            }

            document.getElementById('event_id').value = selected.dataset.id || '';
            document.getElementById('summaryEventName').textContent = selected.dataset.title;
            document.getElementById('summaryEventDate').textContent = selected.dataset.dateLabel;
            document.getElementById('summaryEventTime').textContent = selected.dataset.timeLabel;
            document.getElementById('summaryEventVenue').textContent = selected.dataset.venue;
            document.getElementById('summaryEventType').textContent = selected.dataset.eventType;
            document.getElementById('summaryEventCompensation').textContent = selected.dataset.compensation;
            updateEventMediaPreview(selected);

            if (selected.dataset.compensationType === 'contest') {
                budgetInput.value = '';
                budgetInput.disabled = true;
                budgetOfferField.classList.add('d-none');
                contestPrizeNotice.classList.remove('d-none');
                hourlyRateHelp.classList.add('d-none');
                showContestPrizes(selected);
            } else {
                budgetInput.value = selected.dataset.budget || '';
                budgetInput.disabled = false;
                budgetOfferField.classList.remove('d-none');
                contestPrizeNotice.classList.add('d-none');

                if (selected.dataset.compensationType === 'hourly') {
                    showHourlySuggestion(selected);
                } else {
                    budgetOfferLabel.textContent = 'Budget Offer (₱)';
                    hourlyRateHelp.classList.add('d-none');
                }
            }
            selectedEventSummary.classList.remove('d-none');
    }

    function updateEventMediaPreview(selected) {
        const mediaUrl = selected.dataset.mediaUrl;

        if (!mediaUrl) {
            summaryEventMedia.classList.add('d-none');
            return;
        }

        summaryEventMedia.classList.remove('d-none');

        if (selected.dataset.mediaType === 'video') {
            summaryEventImage.classList.add('d-none');
            summaryEventVideoPreview.classList.remove('d-none');
            return;
        }

        summaryEventImage.src = mediaUrl;
        summaryEventImage.classList.remove('d-none');
        summaryEventVideoPreview.classList.add('d-none');
    }

    function showContestPrizes(selected) {
        const prizes = [];

        if (selected.dataset.firstPrize) {
            prizes.push('1st: ₱' + Number(selected.dataset.firstPrize).toLocaleString());
        }

        if (selected.dataset.secondPrize) {
            prizes.push('2nd: ₱' + Number(selected.dataset.secondPrize).toLocaleString());
        }

        if (selected.dataset.thirdPrize) {
            prizes.push('3rd: ₱' + Number(selected.dataset.thirdPrize).toLocaleString());
        }

        contestPrizeDetails.textContent = prizes.join(' · ');
    }

    function showHourlySuggestion(selected) {
        const start = new Date('2000-01-01T' + selected.dataset.start);
        let end = new Date('2000-01-01T' + selected.dataset.end);

        if (end <= start) {
            end.setDate(end.getDate() + 1);
        }

        const duration = (end - start) / 3600000;
        const rate = Number(selected.dataset.ratePerHour || 0);
        const suggestedOffer = rate * duration;

        budgetInput.value = suggestedOffer.toFixed(2);
        budgetOfferLabel.textContent = 'Suggested Offer (₱)';
        hourlyRateHelp.textContent = '₱' + rate.toLocaleString() + ' per hour × ' + duration + ' hour' + (duration === 1 ? '' : 's') + '. You may edit this offer.';
        hourlyRateHelp.classList.remove('d-none');
    }

    selector.addEventListener('change', updateSelectedEvent);
    updateSelectedEvent();

    });
    </script>

@endsection

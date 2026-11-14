@extends('layouts.app')

@section('title', 'Booking Preview')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.monitoring.bookings') }}" class="btn btn-outline-secondary">Back to Booking Records</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-4">
                <div>
                    <div class="small text-uppercase text-muted fw-semibold mb-1">Booking Preview</div>
                    <h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2>
                    <p class="text-muted mb-0">Event details and booking information.</p>
                </div>
                <span class="badge {{ $booking->statusBadgeClass() }} align-self-sm-start">{{ $booking->statusLabel() }}</span>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Organizer</div>
                    <div class="fw-semibold">{{ $booking->organizer?->fullName() ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Date</div>
                    <div class="fw-semibold">{{ $booking->dateRangeLabel() }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Performer Assignment</div>
                    <div class="fw-semibold">{{ $booking->assignmentDateRangeLabel() }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Time</div>
                    <div class="fw-semibold">{{ $booking->event_time ? \Carbon\Carbon::parse($booking->event_time)->format('g:i A') : '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Venue</div>
                    <div class="fw-semibold">{{ $booking->venue ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Duration</div>
                    <div class="fw-semibold">{{ $booking->duration_hours ? $booking->duration_hours . ' hr' : '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Created</div>
                    <div class="fw-semibold">{{ optional($booking->created_at)->format('F d, Y h:i A') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-semibold">{{ optional($booking->updated_at)->format('F d, Y h:i A') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Contract</div>
                    <div class="fw-semibold">
                        @if($booking->hasSignedContract())
                            Signed contract returned
                        @elseif($booking->hasContract())
                            Contract uploaded
                        @else
                            No contract
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Contract File</div>
                    <div class="fw-semibold">
                        @if($booking->contractUrl())
                            <a href="{{ $booking->contractUrl() }}" target="_blank" rel="noopener">Download contract</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Requirements</div>
                    <div class="fw-semibold">{{ $booking->requirements ?? '—' }}</div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Notes</div>
                    <div class="fw-semibold">{{ $booking->notes ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Booking Summary</h5>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Name</span><span>{{ $booking->event_name }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Date Created</span><span>{{ $booking->created_at->format('M d, Y') }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Scheduled Dates</span><span>{{ $booking->dateRangeLabel() }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Performer Assignment</span><span>{{ $booking->assignmentDateRangeLabel() }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Created By</span><span>{{ $booking->organizer?->fullName() ?? '—' }}</span></div>
        </div>
    </div>
    <div class="col-12">
        <div class="ph-card p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Performers Hired</h4>
                    <p class="text-muted small mb-0">Performers assigned to this event.</p>
                </div>
                <span class="badge rounded-pill text-bg-light border text-dark">
                    {{ $eventPerformers->count() }} {{ \Illuminate\Support\Str::plural('performer', $eventPerformers->count()) }}
                </span>
            </div>
            @if($eventPerformers->isNotEmpty())
                <div class="row g-3">
                    @foreach($eventPerformers as $eventPerformer)
                        @php
                            $performer = $eventPerformer->performer;
                            $performerName = $performer?->fullName() ?? 'Performer information unavailable';
                        @endphp
                        <div class="col-sm-6 col-xl-4">
                            <div class="h-100 d-flex align-items-center gap-3 p-3 rounded-3 border">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 46px; height: 46px; background: rgba(99, 70, 255, .12); color: #6346ff;">
                                    {{ strtoupper(substr($performerName, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-break">{{ $performerName }}</div>
                                    @if($performer?->email)
                                        <div class="small text-muted text-break">{{ $performer->email }}</div>
                                    @else
                                        <div class="small text-muted">Performer</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-3 border p-4 text-center text-muted" style="border-style: dashed !important;">
                    No performers are assigned to this booking.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Monitor Bookings')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
    .booking-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .booking-filter-search,
    .booking-filter-organizer {
        grid-column: span 3;
    }

    .booking-filter-status,
    .booking-filter-from,
    .booking-filter-to,
    .booking-filter-sort {
        grid-column: span 2;
    }

    .booking-filter-actions {
        grid-column: 1 / -1;
        display: flex;
        align-items: end;
        gap: .5rem;
    }

    @media (min-width: 1400px) {
        .booking-filter-grid {
            grid-template-columns: minmax(180px, 1.8fr) minmax(115px, 1.1fr) minmax(180px, 1.8fr) repeat(3, minmax(140px, 1.4fr)) minmax(155px, auto);
            gap: .5rem;
        }

        .booking-filter-grid > * {
            grid-column: auto;
        }

        .booking-filter-actions {
            justify-content: flex-end;
            white-space: nowrap;
        }
    }

    @media (max-width: 767.98px) {
        .booking-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .booking-filter-search,
        .booking-filter-organizer,
        .booking-filter-actions {
            grid-column: 1 / -1;
        }

        .booking-filter-status,
        .booking-filter-from,
        .booking-filter-to,
        .booking-filter-sort {
            grid-column: span 1;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Booking Records</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.monitoring.bookings') }}" class="booking-filter-grid">
        <div class="booking-filter-search">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Event name, venue, requirements" value="{{ request('search') }}">
        </div>
        <div class="booking-filter-status">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                <option value="completed" @selected(request('status') === 'completed')>Booked</option>
                <option value="finished" @selected(request('status') === 'finished')>Completed</option>
                <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>
        <div class="booking-filter-organizer">
            <label class="form-label">Organizer</label>
            <select name="organizer_id" class="form-select ph-input">
                <option value="">All Organizers</option>
                @foreach($organizers as $organizer)
                    <option value="{{ $organizer->id }}" @selected(request('organizer_id') == $organizer->id)>{{ $organizer->fullName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="booking-filter-from">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control ph-input" value="{{ request('date_from') }}">
        </div>
        <div class="booking-filter-to">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control ph-input" value="{{ request('date_to') }}">
        </div>
        @include('admin.partials.sort-filter', ['sortByLabel' => 'Event', 'defaultSort' => 'name_asc', 'sortColumnClass' => 'booking-filter-sort'])
        <div class="booking-filter-actions">
            <button type="submit" class="btn ph-btn-primary">Filter</button>
            <a href="{{ route('admin.monitoring.bookings') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Organizer</th>
                    <th>Hired Performers</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    @php
                        $hiredPerformers = collect($eventPerformers[$b->event_id] ?? []);
                        if ($hiredPerformers->isEmpty() && $b->performer) {
                            $hiredPerformers = collect([[
                                'name' => $b->performer->fullName(),
                                'email' => $b->performer->email,
                            ]]);
                        }
                        $performerCount = $hiredPerformers->count();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $b->event_name }}</strong>
                            @if($b->venue)<div class="small text-muted">{{ $b->venue }}</div>@endif
                        </td>
                        <td>{{ $b->organizer?->fullName() ?? '—' }}</td>
                        <td>
                            @if($performerCount > 0)
                                <div class="d-flex flex-column align-items-start gap-1">
                                    <span class="badge rounded-pill text-bg-light border">
                                        {{ $performerCount }} {{ \Illuminate\Support\Str::plural('performer', $performerCount) }}
                                    </span>
                                    <span class="small fw-semibold">{{ $hiredPerformers->first()['name'] }}</span>
                                    @if($performerCount > 1)
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#eventPerformersModal-{{ $b->id }}">
                                            View performer list
                                        </button>
                                    @else
                                        <span class="small text-muted">{{ $hiredPerformers->first()['email'] }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">No performers assigned</span>
                            @endif
                        </td>
                        <td>
                            {{ $b->assignmentDateRangeLabel() }}
                            @if($b->assignmentDateRangeLabel() !== $b->dateRangeLabel())
                                <div class="small text-muted">Event: {{ $b->dateRangeLabel() }}</div>
                            @endif
                            @if($b->event_time)<div class="small text-muted">{{ \Carbon\Carbon::parse($b->event_time)->format('g:i A') }}</div>@endif
                        </td>
                        <td><span class="badge {{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span></td>
                        <td>{{ $b->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            <a href="{{ route('admin.events.show', $b) }}" class="btn btn-sm btn-outline-info">View booking details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $bookings->appends(request()->query())->links() }}
</div>

@foreach($bookings as $b)
    @php
        $eventModalPerformers = collect($eventPerformers[$b->event_id] ?? []);
        if ($eventModalPerformers->isEmpty() && $b->performer) {
            $eventModalPerformers = collect([[
                'name' => $b->performer->fullName(),
                'email' => $b->performer->email,
            ]]);
        }
    @endphp

    @if($eventModalPerformers->count() > 1)
        <div class="modal fade" id="eventPerformersModal-{{ $b->id }}" tabindex="-1" aria-labelledby="eventPerformersModalLabel-{{ $b->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold" id="eventPerformersModalLabel-{{ $b->id }}">Performers Hired</h5>
                            <div class="small text-muted">{{ $b->event_name }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-column gap-2">
                            @foreach($eventModalPerformers as $performer)
                                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 42px; height: 42px; background: rgba(99, 70, 255, .12); color: #6346ff;">
                                        {{ strtoupper(substr($performer['name'], 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-break">{{ $performer['name'] }}</div>
                                        <div class="small text-muted text-break">{{ $performer['email'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

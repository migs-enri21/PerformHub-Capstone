@extends('layouts.app')

@section('title', 'Organizer Calendar')

@section('sidebar')
    @include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Calendar</h2>
        <p class="text-muted mb-0">View your events and schedule in one place.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="org-panel">
            <div
                class="availability-calendar organizer-calendar"
                data-events='@json($calendarEvents)'
                data-google-busy='@json($googleBusy)'
            >
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="prev" aria-label="Previous month">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h5 class="fw-semibold mb-0 organizer-calendar-month"></h5>
                        <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="next" aria-label="Next month">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="today">Today</button>
                </div>

                <div class="availability-calendar-weekdays">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <span>{{ $weekday }}</span>
                    @endforeach
                </div>

                <div class="availability-calendar-grid" role="grid" aria-label="Organizer event calendar"></div>

                <div class="availability-calendar-legend mt-3">
                    <span><i class="av-legend-dot av-legend-dot--booked"></i> Your event</span>
                    <span><i class="av-legend-dot av-legend-dot--google"></i> Busy on Google Calendar</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="org-right-column">
            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Google Calendar</h6>

                    @if($profile->google_calendar_connected)
                        <span class="badge text-bg-success">Connected</span>
                    @endif
                </div>

                @if($profile->google_calendar_connected)
                    <p class="text-muted small">
                        Busy dates from Google Calendar are shown in gray.
                        @if($profile->google_calendar_synced_at)
                            Last synced {{ $profile->google_calendar_synced_at->diffForHumans() }}.
                        @endif
                    </p>

                    <form method="POST" action="{{ route('organizer.calendar.sync') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm ph-btn-primary">Sync Calendar</button>
                    </form>

                    <form method="POST" action="{{ route('organizer.calendar.disconnect') }}" class="d-inline ms-1">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                    </form>
                @else
                    <p class="text-muted small">Connect Google Calendar to show your external busy dates here.</p>
                    <a href="{{ route('organizer.calendar.connect') }}" class="btn btn-sm ph-btn-outline">
                        <i class="fab fa-google me-1"></i>Connect Google Calendar
                    </a>
                @endif
            </div>

            <div class="org-panel">
                <h6 class="fw-bold mb-3">Upcoming Events</h6>

                @forelse($upcomingEvents as $event)
                    <a href="{{ route('organizer.events.show', $event) }}" class="org-list-item">
                        <span class="org-event-date">{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('d M') }}</span>
                        <div>
                            <strong>{{ $event->title }}</strong>
                            <small class="text-muted d-block">{{ $event->venue }}</small>
                        </div>
                    </a>
                @empty
                    <p class="text-muted small mb-0">No upcoming events yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.organizer-calendar').forEach(calendar => {
    const events = JSON.parse(calendar.dataset.events || '{}');
    const googleBusyDates = JSON.parse(calendar.dataset.googleBusy || '{}');
    const grid = calendar.querySelector('.availability-calendar-grid');
    const monthLabel = calendar.querySelector('.organizer-calendar-month');
    const today = new Date();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();

    function dateKey(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function render() {
        monthLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        grid.innerHTML = '';

        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        for (let index = 0; index < firstDay; index++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'av-day av-day--empty';
            grid.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const key = dateKey(viewYear, viewMonth, day);
            const date = new Date(viewYear, viewMonth, day);
            const event = events[key];
            const googleBusy = googleBusyDates[key];
            const cell = document.createElement(event ? 'a' : 'div');

            cell.className = 'av-day';

            if (event) {
                cell.href = event.url;
                cell.classList.add('av-day--booked', 'organizer-calendar-event');
                cell.title = event.title;
            } else if (googleBusy) {
                cell.classList.add('av-day--google-busy');
                cell.title = googleBusy.summary || 'Busy on Google Calendar';
            }

            if (date < today) {
                cell.classList.add('av-day--past');
            }

            if (key === dateKey(today.getFullYear(), today.getMonth(), today.getDate())) {
                cell.classList.add('av-day--today');
            }

            const number = document.createElement('span');
            number.className = 'av-day-number';
            number.textContent = day;
            cell.appendChild(number);

            if (event) {
                const label = document.createElement('span');
                label.className = 'av-day-event';
                label.textContent = event.title.length > 14 ? `${event.title.slice(0, 14)}…` : event.title;
                cell.appendChild(label);
            } else if (googleBusy) {
                const label = document.createElement('span');
                label.className = 'av-day-google-label';
                label.textContent = 'Google';
                cell.appendChild(label);
            }

            grid.appendChild(cell);
        }
    }

    calendar.querySelectorAll('.organizer-calendar-nav').forEach(button => {
        button.addEventListener('click', () => {
            if (button.dataset.action === 'prev') {
                viewMonth -= 1;
                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear -= 1;
                }
            }

            if (button.dataset.action === 'next') {
                viewMonth += 1;
                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear += 1;
                }
            }

            if (button.dataset.action === 'today') {
                viewYear = today.getFullYear();
                viewMonth = today.getMonth();
            }

            render();
        });
    });

    render();
});
</script>
@endpush

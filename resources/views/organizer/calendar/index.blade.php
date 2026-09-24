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

<div class="modal fade" id="organizerCalendarDayModal" tabindex="-1" aria-labelledby="organizerCalendarDayModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="organizerCalendarDayModalTitle">Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" id="organizerCalendarDayModalDate"></p>
                <div class="d-grid gap-2" id="organizerCalendarDayEvents"></div>
                <div class="alert alert-secondary small mt-3 mb-0 d-none" id="organizerCalendarGoogleBusy"></div>
                <p class="text-muted mb-0 d-none" id="organizerCalendarNoSchedule">No event is scheduled for this day.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn ph-btn-outline" data-bs-dismiss="modal">Close</button>
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
    const modalElement = document.getElementById('organizerCalendarDayModal');
    const modalDate = document.getElementById('organizerCalendarDayModalDate');
    const modalEvents = document.getElementById('organizerCalendarDayEvents');
    const googleBusyMessage = document.getElementById('organizerCalendarGoogleBusy');
    const noScheduleMessage = document.getElementById('organizerCalendarNoSchedule');
    const today = new Date();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();

    function dateKey(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function formatDate(key) {
        const parts = key.split('-');
        const year = Number(parts[0]);
        const month = Number(parts[1]) - 1;
        const day = Number(parts[2]);

        return new Date(year, month, day).toLocaleDateString(undefined, {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
    }

    function formatTime(time) {
        if (!time) {
            return '';
        }

        const parts = time.split(':');
        const hour = Number(parts[0]);
        const minute = parts[1];
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const hourTwelve = hour % 12 || 12;

        return `${hourTwelve}:${minute} ${suffix}`;
    }

    function showDaySchedule(key, dayEvents, googleBusy) {
        if (!modalElement || typeof bootstrap === 'undefined') {
            return;
        }

        modalDate.textContent = formatDate(key);
        modalEvents.innerHTML = '';
        noScheduleMessage.classList.toggle('d-none', dayEvents.length > 0 || googleBusy);
        googleBusyMessage.classList.add('d-none');

        dayEvents.forEach(function (event) {
            const item = document.createElement('div');
            item.className = 'border rounded p-3';

            const title = document.createElement('strong');
            title.textContent = event.title;
            item.appendChild(title);

            const details = document.createElement('p');
            details.className = 'text-muted small mb-2';
            let time = formatTime(event.start_time);

            if (event.end_time) {
                time += ` - ${formatTime(event.end_time)}`;
            }

            details.textContent = `${time} | ${event.venue}`;
            item.appendChild(details);

            const status = document.createElement('span');
            status.className = 'badge bg-secondary me-2';
            status.textContent = event.status;
            item.appendChild(status);

            const viewLink = document.createElement('a');
            viewLink.href = event.url;
            viewLink.className = 'btn btn-sm ph-btn-primary';
            viewLink.textContent = 'View Event';
            item.appendChild(viewLink);

            modalEvents.appendChild(item);
        });

        if (googleBusy) {
            let busyText = googleBusy.summary || 'Busy on Google Calendar';

            if (googleBusy.start_time) {
                busyText += ` (${formatTime(googleBusy.start_time)}`;

                if (googleBusy.end_time) {
                    busyText += ` - ${formatTime(googleBusy.end_time)}`;
                }

                busyText += ')';
            }

            googleBusyMessage.textContent = busyText;
            googleBusyMessage.classList.remove('d-none');
        }

        const modal = new bootstrap.Modal(modalElement);
        modal.show();
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
            const dayEvents = events[key] || [];
            const event = dayEvents[0];
            const googleBusy = googleBusyDates[key];
            const cell = document.createElement('div');

            cell.className = 'av-day';

            if (event) {
                cell.classList.add('av-day--booked', 'organizer-calendar-event');
                cell.title = dayEvents.map(item => item.title).join(', ');
            }

            if (! event && googleBusy) {
                cell.classList.add('av-day--google-busy');
                cell.title = googleBusy.summary || 'Busy on Google Calendar';

                if (googleBusy.start_time) {
                    cell.title += ` (${googleBusy.start_time}`;

                    if (googleBusy.end_time) {
                        cell.title += ` - ${googleBusy.end_time}`;
                    }

                    cell.title += ')';
                }
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
                if (event.title.length > 14) {
                    label.textContent = `${event.title.slice(0, 14)}…`;
                } else {
                    label.textContent = event.title;
                }
                cell.appendChild(label);

                dayEvents.slice(1).forEach(item => {
                    const more = document.createElement('span');
                    more.className = 'av-day-event';
                    if (item.title.length > 14) {
                        more.textContent = `${item.title.slice(0, 14)}...`;
                    } else {
                        more.textContent = item.title;
                    }
                    cell.appendChild(more);
                });
            } else if (googleBusy) {
                const label = document.createElement('span');
                label.className = 'av-day-google-label';
                label.textContent = 'Google';

                if (googleBusy.start_time) {
                    label.textContent += ` ${googleBusy.start_time}`;
                }
                cell.appendChild(label);
            }

            if (dayEvents.length > 0 || googleBusy) {
                cell.classList.add('organizer-calendar-day--clickable');
                cell.setAttribute('role', 'button');
                cell.setAttribute('tabindex', '0');
                cell.setAttribute('aria-label', `View schedule for ${formatDate(key)}`);
                cell.addEventListener('click', function () {
                    showDaySchedule(key, dayEvents, googleBusy);
                });
                cell.addEventListener('keydown', function (keyboardEvent) {
                    if (keyboardEvent.key === 'Enter' || keyboardEvent.key === ' ') {
                        keyboardEvent.preventDefault();
                        showDaySchedule(key, dayEvents, googleBusy);
                    }
                });
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

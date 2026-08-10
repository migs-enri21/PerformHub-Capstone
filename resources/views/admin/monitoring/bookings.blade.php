@extends('layouts.app')

@section('title', 'Monitor Bookings')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Booking Records</h2>
<div class="ph-card p-0 overflow-hidden">
    @push('styles')
    <style>
        .ph-highlight{box-shadow:0 0 0 4px rgba(255,193,7,0.25);transition:box-shadow .5s ease-in-out}
        .booking-filters .form-control,.booking-filters .form-select{min-height:38px}
    </style>
    @endpush

    <div id="bookingFilters" class="booking-filters p-3 border-bottom">
        <form class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <input id="bookingSearch" type="text" class="form-control form-control-sm" placeholder="Event, organizer, performer">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select id="bookingStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Organizer</label>
                <select id="bookingOrganizer" class="form-select form-select-sm">
                    <option value="">All Organizers</option>
                    @foreach(($organizersForFilter ?? $organizers ?? collect()) as $org)
                        <option value="{{ strtolower($org->name) }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" id="bookingResetFilters" class="btn btn-outline-secondary btn-sm">Reset</button>
            </div>
        </form>
    </div>

    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Performer</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($bookings as $b)
                <tr>
                    <td>{{ $b->event_name }}</td>
                    <td>{{ $b->organizer->name }}</td>
                    <td>{{ $b->performer->name }}</td>
                    <td>{{ optional($b->event_date)->format('M d, Y') }}</td>
                    <td><span class="badge {{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span></td>
                    <td><a href="{{ route('admin.events.show', $b) }}" class="btn btn-sm btn-outline-info">Preview</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $bookings->links() }}
@push('scripts')
<script>
    (function(){
        const searchInput = document.getElementById('bookingSearch');
        const statusSelect = document.getElementById('bookingStatus');
        const organizerSelect = document.getElementById('bookingOrganizer');
        const resetBtn = document.getElementById('bookingResetFilters');
        const filters = document.getElementById('bookingFilters');
        const table = document.querySelector('.ph-card table tbody');

        function filterRows(){
            const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const status = statusSelect ? statusSelect.value.toLowerCase() : '';
            const organizer = organizerSelect ? organizerSelect.value.toLowerCase() : '';
            table.querySelectorAll('tr').forEach(tr => {
                const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim().toLowerCase());
                const matchesQuery = q === '' || cols.some(c => c.includes(q));
                const matchesStatus = status === '' || cols.some(c => c.includes(status));
                const matchesOrganizer = organizer === '' || cols.some(c => c.includes(organizer));
                tr.style.display = (matchesQuery && matchesStatus && matchesOrganizer) ? '' : 'none';
            });
        }

        function resetFilters(){
            if(searchInput) searchInput.value = '';
            if(statusSelect) statusSelect.value = '';
            if(organizerSelect) organizerSelect.value = '';
            filterRows();
        }

        if(searchInput) searchInput.addEventListener('input', filterRows);
        if(statusSelect) statusSelect.addEventListener('change', filterRows);
        if(organizerSelect) organizerSelect.addEventListener('change', filterRows);
        if(resetBtn) resetBtn.addEventListener('click', resetFilters);

        // highlight so user can spot the new filter area
        try{ filters.classList.add('ph-highlight'); setTimeout(()=>filters.classList.remove('ph-highlight'), 1800); }catch(e){}

        // initial pass
        filterRows();
    })();
</script>
@endpush
@endsection

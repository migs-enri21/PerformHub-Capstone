@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Admin Dashboard</h2>
<div class="row g-4 mb-4">
    @foreach([['label'=>'Total Users','value'=>$stats['users']],['label'=>'Performers','value'=>$stats['performers']],['label'=>'Organizers','value'=>$stats['organizers']],['label'=>'Bookings','value'=>$stats['bookings']],['label'=>'Pending Verifications','value'=>$stats['pending_verifications']]] as $stat)
        <div class="col-md-4 col-lg-2">
            <div class="ph-card p-3 stat-card text-center">
                <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                <small class="text-muted">{{ $stat['label'] }}</small>
            </div>
        </div>
    @endforeach
</div>
<div class="ph-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Recent Bookings</h5>
        {{--<div>
            <button id="dashboardToggleFilters" class="btn btn-outline-secondary btn-sm me-2">Filter</button>
            <button id="dashboardResetFilters" class="btn btn-outline-secondary btn-sm">Reset</button>
        </div>--}}
    </div>

    <div id="dashboardFilters" class="mb-3" style="display:none;">
        <form class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <input id="dashboardSearch" type="text" class="form-control form-control-sm" placeholder="Event, organizer, performer">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select id="dashboardStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Organizer</label>
                <select id="dashboardOrganizer" class="form-select form-select-sm">
                    <option value="">All Organizers</option>
                    @foreach($organizersForFilter as $org)
                        <option value="{{ strtolower($org->name) }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
           {{-- <div class="col-md-2 text-end">
                <button type="button" id="dashboardApplyFilters" class="btn ph-btn-primary btn-sm">Apply</button>
            </div> --}}
        </form> 
    </div>

    <table class="table table-dark table-sm mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Performer</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($recentBookings as $b)
                <tr>
                    <td>{{ $b->event_name }}</td>
                    <td>{{ $b->organizer->name }}</td>
                    <td>{{ $b->performer->name }}</td>
                    <td><span class="badge {{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@push('scripts')
<script>
    (function(){
        const toggleBtn = document.getElementById('dashboardToggleFilters');
        const resetBtn = document.getElementById('dashboardResetFilters');
        const filters = document.getElementById('dashboardFilters');
        const applyBtn = document.getElementById('dashboardApplyFilters');
        const searchInput = document.getElementById('dashboardSearch');
        const statusSelect = document.getElementById('dashboardStatus');
        const organizerSelect = document.getElementById('dashboardOrganizer');
        const table = document.querySelector('.ph-card table tbody');

        if(toggleBtn) toggleBtn.addEventListener('click', () => {
            filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
        });

        function resetFilters(){
            if(searchInput) searchInput.value = '';
            if(statusSelect) statusSelect.value = '';
            if(organizerSelect) organizerSelect.value = '';
            filterRows();
        }

        if(resetBtn) resetBtn.addEventListener('click', resetFilters);
        if(applyBtn) applyBtn.addEventListener('click', filterRows);

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
    })();
</script>
@endpush
@endsection

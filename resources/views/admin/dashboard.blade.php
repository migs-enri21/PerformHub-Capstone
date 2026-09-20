@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Admin Dashboard</h2>
<div class="row g-4 mb-4">
    @foreach([
        ['label'=>'Total Users','value'=>$stats['users'],'url'=>route('admin.users.index')],
        ['label'=>'Performers','value'=>$stats['performers'],'url'=>route('admin.users.index', ['role'=>'performer'])],
        ['label'=>'Organizers','value'=>$stats['organizers'],'url'=>route('admin.users.index', ['role'=>'organizer'])],
        ['label'=>'Bookings','value'=>$stats['bookings'],'url'=>route('admin.monitoring.bookings')],
        ['label'=>'Pending Verifications','value'=>$stats['pending_verifications'],'url'=>route('admin.users.index', ['verification'=>'pending'])],
        ['label'=>'Unread Alerts','value'=>$stats['unread_notifications'],'url'=>route('notifications.index')],
    ] as $stat)
        <div class="col-md-4 col-lg-2">
            <a href="{{ $stat['url'] }}" class="ph-card p-3 stat-card text-center text-decoration-none d-block">
                <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                <small class="text-muted">{{ $stat['label'] }}</small>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="ph-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Recent Bookings</h5>
                <a href="{{ route('admin.monitoring.bookings') }}" class="btn btn-sm btn-outline-primary">View All Bookings</a>
            </div>

            <div class="list-group list-group-flush">
                @forelse($recentBookings as $b)
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $b->event_name }}</div>
                            <small class="text-muted">{{ $b->organizer?->fullName() ?? '—' }} · {{ $b->performer?->fullName() ?? '—' }}</small>
                        </div>
                        <span class="badge {{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span>
                    </div>
                @empty
                    <div class="text-muted py-3">No recent bookings yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

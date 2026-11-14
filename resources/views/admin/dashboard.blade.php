@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
    .admin-stat-card {
        height: 100%;
        border-left-color: var(--stat-accent) !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .admin-stat-card:hover,
    .admin-stat-card:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(34, 24, 84, .1);
    }

    .admin-stat-icon,
    .admin-booking-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 12px;
    }

    .admin-stat-icon {
        width: 44px;
        height: 44px;
        color: var(--stat-accent);
        background: var(--stat-icon-bg);
    }

    .admin-stat-value {
        color: var(--stat-accent);
        font-size: 1.55rem;
        line-height: 1.1;
    }

    .admin-booking-icon {
        width: 40px;
        height: 40px;
        color: #6346ff;
        background: rgba(99, 70, 255, .1);
    }

    .admin-booking-content {
        min-width: 0;
    }

    .admin-booking-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem 1rem;
    }

    .admin-recent-booking-link {
        border-radius: .75rem;
        transition: background-color .18s ease, transform .18s ease;
    }

    .admin-recent-booking-link:hover {
        background-color: rgba(99, 70, 255, .045);
        transform: translateX(2px);
    }

    .admin-recent-booking-link:focus-visible {
        outline: 2px solid #6346ff;
        outline-offset: -2px;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">A quick overview of activity across PerformHub.</p>
    </div>
</div>
<div class="row g-4 mb-4">
    @foreach([
        ['label'=>'Total Users','value'=>$stats['users'],'url'=>route('admin.users.index'),'icon'=>'fa-users','accent'=>'#6346ff','icon_bg'=>'rgba(99,70,255,.1)'],
        ['label'=>'Performers','value'=>$stats['performers'],'url'=>route('admin.users.index', ['role'=>'performer']),'icon'=>'fa-music','accent'=>'#0284c7','icon_bg'=>'rgba(2,132,199,.1)'],
        ['label'=>'Organizers','value'=>$stats['organizers'],'url'=>route('admin.users.index', ['role'=>'organizer']),'icon'=>'fa-building','accent'=>'#059669','icon_bg'=>'rgba(5,150,105,.1)'],
        ['label'=>'Bookings','value'=>$stats['bookings'],'url'=>route('admin.monitoring.bookings'),'icon'=>'fa-ticket','accent'=>'#d97706','icon_bg'=>'rgba(217,119,6,.1)'],
        ['label'=>'Pending Verifications','value'=>$stats['pending_verifications'],'url'=>route('admin.users.index', ['verification'=>'pending']),'icon'=>'fa-user-clock','accent'=>'#e11d48','icon_bg'=>'rgba(225,29,72,.1)'],
        ['label'=>'Unread Alerts','value'=>$stats['unread_notifications'],'url'=>route('notifications.index'),'icon'=>'fa-bell','accent'=>'#475569','icon_bg'=>'rgba(71,85,105,.1)'],
    ] as $stat)
        <div class="col-md-4 col-xxl-2">
            <a href="{{ $stat['url'] }}" class="ph-card p-3 stat-card admin-stat-card text-decoration-none d-block" style="--stat-accent: {{ $stat['accent'] }}; --stat-icon-bg: {{ $stat['icon_bg'] }};">
                <div class="d-flex align-items-center gap-3">
                    <span class="admin-stat-icon"><i class="fas {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                    <span class="text-start">
                        <span class="admin-stat-value fw-bold d-block">{{ $stat['value'] }}</span>
                        <span class="small text-muted">{{ $stat['label'] }}</span>
                    </span>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="ph-card p-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-semibold mb-1">Recent Bookings</h5>
            <p class="text-muted small mb-0">The latest event bookings on the platform.</p>
        </div>
        <a href="{{ route('admin.monitoring.bookings') }}" class="btn btn-sm btn-outline-primary">View All Bookings</a>
    </div>
    <div class="list-group list-group-flush">
        @forelse($recentBookings as $b)
            <a href="{{ route('admin.events.show', $b) }}" class="list-group-item list-group-item-action admin-recent-booking-link text-decoration-none text-body px-2 py-3 d-flex justify-content-between align-items-center gap-3 border-0 border-bottom" aria-label="View booking details for {{ $b->event_name }}">
                <div class="d-flex align-items-start gap-3 flex-grow-1 admin-booking-content">
                    <span class="admin-booking-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                    <div class="flex-grow-1 admin-booking-content">
                        <div class="fw-semibold text-truncate">{{ $b->event_name }}</div>
                        <div class="admin-booking-meta mt-2">
                            <small class="text-muted text-break">
                                <i class="fas fa-building me-1" aria-hidden="true"></i>
                                Organizer: <span class="text-body">{{ $b->organizer?->fullName() ?? '—' }}</span>
                            </small>
                            <small class="text-muted text-break">
                                <i class="fas fa-music me-1" aria-hidden="true"></i>
                                Performer: <span class="text-body">{{ $b->performer?->fullName() ?? '—' }}</span>
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-calendar-day me-1" aria-hidden="true"></i>
                                {{ $b->assignmentDateRangeLabel() }}
                            </small>
                        </div>
                    </div>
                </div>
                <span class="badge {{ $b->statusBadgeClass() }} text-nowrap flex-shrink-0">{{ $b->statusLabel() }}</span>
            </a>
        @empty
            <div class="rounded-3 border p-4 text-center text-muted">
                <i class="fas fa-calendar-xmark d-block fs-4 mb-2" aria-hidden="true"></i>
                No recent bookings yet.
            </div>
        @endforelse
    </div>
</div>
@endsection

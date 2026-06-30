@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Admin Dashboard</h2>
<div class="row g-4 mb-4">
    @foreach([['label'=>'Total Users','value'=>$stats['users']],['label'=>'Performers','value'=>$stats['performers']],['label'=>'Organizers','value'=>$stats['organizers']],['label'=>'Bookings','value'=>$stats['bookings']],['label'=>'Pending Verifications','value'=>$stats['pending_verifications']],['label'=>'Interviews','value'=>$stats['interviews']]] as $stat)
        <div class="col-md-4 col-lg-2">
            <div class="ph-card p-3 stat-card text-center">
                <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                <small class="text-muted">{{ $stat['label'] }}</small>
            </div>
        </div>
    @endforeach
</div>
<div class="ph-card p-4">
    <h5 class="fw-semibold mb-3">Recent Bookings</h5>
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
@endsection

@extends('layouts.app')

@section('title', 'Monitor Interviews')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Interview Schedules</h2>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Booking</th><th>Organizer</th><th>Performer</th><th>Scheduled</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach($interviews as $i)
                <tr>
                    <td>{{ $i->booking->event_name }}</td>
                    <td>{{ $i->organizer->name }}</td>
                    <td>{{ $i->performer->name }}</td>
                    <td>{{ $i->scheduled_at->format('M d, Y g:i A') }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($i->status) }}</span></td>
                    <td><a href="{{ route('interviews.join', $i) }}" class="btn btn-sm ph-btn-outline">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $interviews->links() }}
@endsection

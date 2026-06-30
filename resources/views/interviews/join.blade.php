@extends('layouts.app')

@section('title', 'Interview Room')

@section('content')
<div class="mb-3">
    <h2 class="fw-bold mb-1">{{ $interview->booking->event_name }}</h2>
    <p class="text-muted mb-0">Scheduled: {{ $interview->scheduled_at->format('F d, Y g:i A') }}</p>
</div>
<div class="ph-card overflow-hidden" style="height: 70vh;">
    <div id="jitsi-container" style="width:100%;height:100%;"></div>
</div>
@endsection

@push('scripts')
<script src="https://meet.jit.si/external_api.js"></script>
<script>
const domain = 'meet.jit.si';
const options = {
    roomName: '{{ $interview->jitsi_room_id }}',
    width: '100%',
    height: '100%',
    parentNode: document.querySelector('#jitsi-container'),
    userInfo: { displayName: '{{ auth()->user()->name }}' }
};
const api = new JitsiMeetExternalAPI(domain, options);
</script>
@endpush

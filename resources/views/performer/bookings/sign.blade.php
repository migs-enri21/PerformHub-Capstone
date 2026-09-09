@extends('layouts.app')

@section('title', 'Sign Contract')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Sign Contract</h2>
        <p class="text-muted mb-0">Review and sign the contract for {{ $booking->event_name }}.</p>
    </div>
    <a href="{{ route('performer.bookings.show', $booking) }}" class="btn ph-btn-outline btn-sm">Back to Booking</a>
</div>

<div class="ph-card p-4">
    <div id="signwell-signing-area" data-signing-url="{{ $signingUrl }}"></div>

    <form id="signature-complete-form" method="POST" action="{{ route('performer.bookings.signature.sync', $booking) }}">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<style>
    #signwell-signing-area {
        min-height: 760px;
    }

    #signwell-signing-area iframe {
        width: 100% !important;
        min-height: 760px !important;
        height: 760px !important;
    }
</style>
<script src="https://static.signwell.com/assets/embedded.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var signingArea = document.getElementById('signwell-signing-area');
    var signingUrl = signingArea.dataset.signingUrl;

    var signWellEmbed = new SignWellEmbed({
        url: signingUrl,
        containerId: 'signwell-signing-area',
        allowClose: true,
        events: {
            completed: function () {
                document.getElementById('signature-complete-form').submit();
            }
        }
    });

    signWellEmbed.open();
});
</script>
@endpush

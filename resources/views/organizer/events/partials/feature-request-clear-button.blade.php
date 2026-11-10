@php
    $hasReviewedRequests = false;

    foreach ($requests as $featureRequest) {
        if ($featureRequest->status === 'approved' || $featureRequest->status === 'rejected') {
            $hasReviewedRequests = true;
        }
    }
@endphp

@if($hasReviewedRequests)
    <form method="POST" action="{{ route('organizer.feature-requests.clear-reviewed', $requestType) }}" class="ms-auto me-3">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn ph-btn-outline btn-sm">Clear approved and rejected</button>
    </form>
@endif

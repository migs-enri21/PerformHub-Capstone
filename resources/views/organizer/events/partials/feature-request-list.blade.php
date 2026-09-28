@php
    $hasReviewedRequests = false;

    foreach($requests as $featureRequest) {
        if ($featureRequest->status === 'approved' || $featureRequest->status === 'rejected') {
            $hasReviewedRequests = true;
        }
    }
@endphp

@if($requests->isEmpty())
    <p class="text-muted mb-0">You have not sent any requests yet.</p>
@else
    @if($hasReviewedRequests)
        <form method="POST" action="{{ route('organizer.feature-requests.clear-reviewed', $requestType) }}" class="mb-3 text-end">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-secondary">Clear approved and rejected</button>
        </form>
    @endif
    <div class="list-group list-group-flush">
        @foreach($requests as $featureRequest)
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold">{{ $featureRequest->name }}</div>
                        @if($featureRequest->description)
                            <div class="small text-muted">{{ $featureRequest->description }}</div>
                        @endif
                        <div class="small text-muted mt-1">Sent {{ $featureRequest->created_at->format('M d, Y') }}</div>
                    </div>
                    @if($featureRequest->status === 'approved')
                        <span class="badge text-bg-success">Approved</span>
                    @elseif($featureRequest->status === 'rejected')
                        <span class="badge text-bg-danger">Rejected</span>
                    @else
                        <span class="badge text-bg-warning">Pending</span>
                    @endif
                </div>
                @if($featureRequest->status === 'pending')
                    <form method="POST" action="{{ route('organizer.feature-requests.destroy', $featureRequest) }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Retract request</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
@endif

@extends('layouts.app')

@section('title', 'Feature Requests')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Feature Requests</h2>
        <p class="text-muted mb-0">Review requests for missing categories, genres, event types and specialties.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.feature-requests.index') }}" class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label" for="featureRequestSearch">Search</label>
            <input type="search" name="search" id="featureRequestSearch" class="form-control ph-input" placeholder="Option, requester, or description" value="{{ $search }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label" for="featureRequestType">Type</label>
            <select name="type" id="featureRequestType" class="form-select ph-input">
                <option value="">All types</option>
                <option value="category" @selected($type === 'category')>Category</option>
                <option value="event_type" @selected($type === 'event_type')>Event Type</option>
                <option value="genre" @selected($type === 'genre')>Genre</option>
                <option value="specialty" @selected($type === 'specialty')>Specialty</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="featureRequestStatus">Status</label>
            <select name="status" id="featureRequestStatus" class="form-select ph-input">
                <option value="">All statuses</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.feature-requests.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Requested Option</th>
                    <th>Type</th>
                    <th>Requested By</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $featureRequest)
                    <tr>
                        <td class="align-middle fw-semibold">{{ $featureRequest->name }}</td>
                        <td class="align-middle">
                            {{ $featureRequest->typeLabel() }}
                            @if($featureRequest->category)
                                <div class="small text-muted">{{ $featureRequest->category->name }}</div>
                            @endif
                        </td>
                        <td class="align-middle">{{ $featureRequest->requester->fullName() }}</td>
                        <td class="align-middle">{{ $featureRequest->description ?: '—' }}</td>
                        <td class="align-middle">
                            <span class="badge {{ $featureRequest->status === 'pending' ? 'bg-warning text-dark' : ($featureRequest->status === 'approved' ? 'bg-success' : 'bg-secondary') }}">
                                {{ ucfirst($featureRequest->status) }}
                            </span>
                        </td>
                        <td class="align-middle text-end text-nowrap pe-4">
                            @if($featureRequest->status === 'pending')
                                <form method="POST" action="{{ route('admin.feature-requests.approve', $featureRequest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#declineFeatureRequestModal"
                                    data-feature-request-id="{{ $featureRequest->id }}"
                                    data-feature-request-name="{{ $featureRequest->name }}"
                                    data-reject-url="{{ route('admin.feature-requests.reject', $featureRequest) }}"
                                >Decline</button>
                            @else
                                <small class="text-muted">{{ $featureRequest->reviewed_at?->diffForHumans() }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No feature requests yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $requests->links() }}</div>

<div class="modal fade" id="declineFeatureRequestModal" tabindex="-1" aria-labelledby="declineFeatureRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="declineFeatureRequestForm" class="modal-content">
            @csrf
            <input type="hidden" name="feature_request_id" id="declineFeatureRequestId" value="{{ old('feature_request_id') }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="declineFeatureRequestModalLabel">Decline feature request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Add a reason for declining <strong id="declineFeatureRequestName"></strong>. The requester will receive it as a message.</p>
                <label class="form-label" for="declineFeatureRequestReason">Reason</label>
                <textarea name="reason" id="declineFeatureRequestReason" class="form-control @error('reason') is-invalid @enderror" rows="4" maxlength="1000" required>{{ old('reason') }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Decline request</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('declineFeatureRequestModal');
    const form = document.getElementById('declineFeatureRequestForm');
    const requestId = document.getElementById('declineFeatureRequestId');
    const requestName = document.getElementById('declineFeatureRequestName');

    modalElement.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        form.action = trigger.dataset.rejectUrl;
        requestId.value = trigger.dataset.featureRequestId;
        requestName.textContent = trigger.dataset.featureRequestName;
    });

    @if($errors->has('reason') && old('feature_request_id'))
        const trigger = document.querySelector('[data-feature-request-id="{{ old('feature_request_id') }}"]');
        if (trigger && typeof bootstrap !== 'undefined') {
            form.action = trigger.dataset.rejectUrl;
            requestName.textContent = trigger.dataset.featureRequestName;
            new bootstrap.Modal(modalElement).show();
        }
    @endif
});
</script>
@endpush
@endsection
@extends('layouts.app')

@section('title', 'Feature Requests')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Feature Requests</h2>
        <p class="text-muted mb-0">Review organizer requests for missing categories and event types.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
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
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $featureRequest)
                    <tr>
                        <td class="align-middle fw-semibold">{{ $featureRequest->name }}</td>
                        <td class="align-middle">{{ $featureRequest->typeLabel() }}</td>
                        <td class="align-middle">{{ $featureRequest->requester->fullName() }}</td>
                        <td class="align-middle">{{ $featureRequest->description ?: '—' }}</td>
                        <td class="align-middle">
                            <span class="badge {{ $featureRequest->status === 'pending' ? 'bg-warning text-dark' : ($featureRequest->status === 'approved' ? 'bg-success' : 'bg-secondary') }}">
                                {{ ucfirst($featureRequest->status) }}
                            </span>
                        </td>
                        <td class="align-middle text-end text-nowrap">
                            @if($featureRequest->status === 'pending')
                                <form method="POST" action="{{ route('admin.feature-requests.approve', $featureRequest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.feature-requests.reject', $featureRequest) }}" class="d-inline" onsubmit="return confirm('Decline this request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Decline</button>
                                </form>
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
@endsection
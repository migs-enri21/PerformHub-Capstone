@extends('onboarding.layout', ['title' => 'Choose Your Role', 'current' => 1])

@section('onboarding-content')
<h2 class="fw-bold text-center mb-1">Join PerformHub</h2>
<p class="text-muted text-center mb-4">Tell us how you'll use the platform</p>

<form method="POST" action="{{ route('onboarding.role.store') }}">
    @csrf
    <input type="hidden" name="role" id="roleInput" value="{{ old('role', $user->role) }}">

    <div class="row g-3 mb-4">
        @foreach([
            'performer' => ['icon' => 'fa-microphone', 'title' => 'I am a Performer', 'desc' => 'Showcase your talent, manage bookings, and grow your audience.'],
            'organizer' => ['icon' => 'fa-building', 'title' => 'I am an Organizer', 'desc' => 'Find and book performers for your events and venues.'],
        ] as $key => $item)
            <div class="col-12">
                <div class="onboarding-role-card {{ old('role', $user->role) === $key ? 'active' : '' }}" data-role="{{ $key }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="onboarding-role-icon"><i class="fas {{ $item['icon'] }}"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1">{{ $item['title'] }}</h6>
                            <p class="text-muted small mb-0">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn ph-btn-primary w-100">
        Continue <i class="fas fa-arrow-right ms-2"></i>
    </button>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.onboarding-role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.onboarding-role-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('roleInput').value = card.dataset.role;
    });
});
</script>
@endpush

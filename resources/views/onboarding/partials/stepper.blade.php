@php
    $steps = $steps ?? [
        1 => 'Profile',
        2 => 'Verification',
        3 => 'Done',
    ];
@endphp
<div class="onboarding-stepper mb-4">
    @foreach($steps as $num => $label)
        <div class="onboarding-step {{ $num < $current ? 'completed' : ($num === $current ? 'active' : '') }}">
            <div class="onboarding-step-circle">
                @if($num < $current)
                    @if($num === 1)
                        <a href="{{ route('onboarding.profile') }}" class="text-decoration-none text-reset" aria-label="Edit profile">
                            <i class="fas fa-check"></i>
                        </a>
                    @else
                        <i class="fas fa-check"></i>
                    @endif
                @else
                    {{ $num }}
                @endif
            </div>
            @if($num === 1 && $num < $current)
                <a href="{{ route('onboarding.profile') }}" class="onboarding-step-label text-decoration-none">{{ $label }}</a>
            @else
                <span class="onboarding-step-label">{{ $label }}</span>
            @endif
        </div>
        @if(!$loop->last)
            <div class="onboarding-step-line {{ $num < $current ? 'completed' : '' }}"></div>
        @endif
    @endforeach
</div>

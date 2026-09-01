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
                    <i class="fas fa-check"></i>
                @else
                    {{ $num }}
                @endif
            </div>
            <span class="onboarding-step-label">{{ $label }}</span>
        </div>
        @if(!$loop->last)
            <div class="onboarding-step-line {{ $num < $current ? 'completed' : '' }}"></div>
        @endif
    @endforeach
</div>

@props(['performer', 'editable' => false])

@php
    $stats = $performer->socialStats();
    $missingCounts = collect($stats)->contains(fn ($stat) => $stat['count'] === null);
@endphp

@if(count($stats))
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Social Media</h5>

        @if($editable && $missingCounts)
            <p class="text-muted small mb-3">
                Add follower counts in <a href="{{ route('performer.profile.edit') }}">Edit Profile</a> (e.g. enter <code>19000</code> to show as 19K).
            </p>
        @endif

        <div class="row g-3">
            @foreach($stats as $platform => $stat)
                <div class="col-6 col-lg-3">
                    <a href="{{ $stat['url'] }}" target="_blank" rel="noopener" class="social-stat-card social-stat-card--{{ $platform }}">
                        <span class="social-stat-icon">
                            <i class="fab fa-{{ $platform }}"></i>
                        </span>
                        <span class="social-stat-body">
                            @if($stat['count'] !== null)
                                <span class="social-stat-count">{{ $stat['formatted'] }}</span>
                                <span class="social-stat-metric">{{ $stat['metric'] }}</span>
                            @else
                                <span class="social-stat-count social-stat-count--empty">—</span>
                                <span class="social-stat-metric">Add {{ strtolower($stat['metric']) }}</span>
                            @endif
                            <span class="social-stat-platform">{{ $stat['label'] }}</span>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

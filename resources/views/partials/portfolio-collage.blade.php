@props([
    'items',
    'editable' => false,
])

@php
    $count = $items->count();
    $layout = match (true) {
        $count === 1 => 'portfolio-collage--1',
        $count === 2 => 'portfolio-collage--2',
        $count === 3 => 'portfolio-collage--3',
        $count === 4 => 'portfolio-collage--4',
        default => 'portfolio-collage--many',
    };
    $visible = $count > 4 ? $items->take(4) : $items;
    $caption = $items->first()->caption;
@endphp

<article class="portfolio-feed-card">
    <div class="portfolio-preview-collage portfolio-feed-collage {{ $layout }}">
        @foreach($visible as $index => $item)
            <div class="portfolio-collage-tile">
                @if($item->type === 'photo')
                    <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $caption ?? '' }}">
                @else
                    <video src="{{ asset('storage/'.$item->file_path) }}" controls playsinline></video>
                    <span class="portfolio-collage-badge"><i class="fas fa-play me-1"></i>Video</span>
                @endif
                @if($count > 4 && $index === 3)
                    <div class="portfolio-collage-more">+{{ $count - 4 }}</div>
                @endif
            </div>
        @endforeach
    </div>

    @if($caption || $editable)
        <div class="portfolio-feed-footer p-3">
            @if($caption)
                <p class="mb-0 small">{{ $caption }}</p>
            @endif
            @if($editable)
                <div class="d-flex flex-wrap gap-2 {{ $caption ? 'mt-2' : '' }}">
                    @foreach($items as $item)
                        <form action="{{ route('performer.portfolio.destroy', $item) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove {{ $item->type }}</button>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</article>

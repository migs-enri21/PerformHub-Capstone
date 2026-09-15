@props([
    'categories',
    'selectedCategoryIds' => [],
    'inputIdPrefix' => 'portfolio-cat',
])

@php
    $selected = collect(old('category_ids', $selectedCategoryIds))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="portfolio-sketch-field">
    <span class="portfolio-sketch-label">What are you doing in this sample? <span class="text-danger">*</span></span>
    <p class="text-muted small mb-2">Choose this first. Organizers need to know if you are singing, dancing, hosting, or another role in these photos or videos.</p>
    <div class="category-checkbox-grid">
        @foreach($categories as $cat)
            <label class="category-checkbox-option">
                <input
                    type="checkbox"
                    name="category_ids[]"
                    value="{{ $cat->id }}"
                    id="{{ $inputIdPrefix }}-{{ $cat->id }}"
                    @checked(in_array($cat->id, $selected, true))
                >
                <span>{{ $cat->name }}</span>
            </label>
        @endforeach
    </div>
    @error('category_ids')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error('category_ids.*')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

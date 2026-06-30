@props([
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
])

@php
    use App\Support\PerformerGenres;
    $selected = old($name, $value);
    $options = PerformerGenres::all();
@endphp

<select name="{{ $name }}" class="form-select ph-input" {{ $required ? 'required' : '' }}>
    <option value="" @if($required) disabled @endif {{ $selected ? '' : 'selected' }}>{{ $placeholder }}</option>
    @if($selected && ! in_array($selected, $options, true))
        <option value="{{ $selected }}" selected>{{ $selected }}</option>
    @endif
    @foreach($options as $genre)
        <option value="{{ $genre }}" @selected($selected === $genre)>{{ $genre }}</option>
    @endforeach
</select>

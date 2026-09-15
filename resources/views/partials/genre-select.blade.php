@props([
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
<<<<<<< HEAD
    'id' => null,
])

@php
    use App\Support\PerformerGenres;
    $selected = old($name, $value);
    $options = PerformerGenres::all();
@endphp

<select name="{{ $name }}" class="form-select ph-input" @if($id) id="{{ $id }}" @endif {{ $required ? 'required' : '' }}>
    <option value="" @if($required) disabled @endif {{ $selected ? '' : 'selected' }}>{{ $placeholder }}</option>
    @if($selected && ! in_array($selected, $options, true))
        <option value="{{ $selected }}" selected>{{ $selected }}</option>
    @endif
    @foreach($options as $genre)
        <option value="{{ $genre }}" @selected($selected === $genre)>{{ $genre }}</option>
    @endforeach
</select>
=======
    'multiple' => false,
])

@include('partials.ph-select', [
    'name' => $name,
    'value' => $value,
    'required' => $required,
    'placeholder' => $placeholder,
    'multiple' => $multiple,
    'options' => \App\Support\PerformerGenres::all(),
])
>>>>>>> aa1360cfacd5a024188ed2d7830473856d778044

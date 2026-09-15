@props([
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
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

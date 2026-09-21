@props([
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
    'multiple' => false,
    'groups' => null,
    'guidedKind' => null,
    'emptyMessage' => 'Select a category first',
])

@include('partials.ph-select', [
    'name' => $name,
    'value' => $value,
    'required' => $required,
    'placeholder' => $placeholder,
    'multiple' => $multiple,
    'groups' => $groups,
    'guidedKind' => $guidedKind,
    'emptyMessage' => $emptyMessage,
    'options' => $groups === null ? \App\Support\PerformerGenres::all() : [],
])

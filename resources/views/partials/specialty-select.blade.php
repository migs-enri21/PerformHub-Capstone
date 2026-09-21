@props([
    'name' => 'specialty',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select specialty / instrument',
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
    'options' => $groups === null ? \App\Support\PerformerSpecialties::all() : [],
])

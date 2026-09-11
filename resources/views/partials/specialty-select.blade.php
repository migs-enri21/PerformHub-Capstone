@props([
    'name' => 'specialty',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select specialty / instrument',
    'multiple' => false,
])

@include('partials.ph-select', [
    'name' => $name,
    'value' => $value,
    'required' => $required,
    'placeholder' => $placeholder,
    'multiple' => $multiple,
    'options' => \App\Support\PerformerSpecialties::all(),
])

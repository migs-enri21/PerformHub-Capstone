@props([
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => 'Select',
    'required' => false,
    'multiple' => false,
])

@php
    $rawSelected = old($name, $value);

    if ($multiple) {
        if (is_array($rawSelected)) {
            $selected = array_values(array_filter($rawSelected, fn ($item) => is_string($item) && $item !== ''));
        } elseif (is_string($rawSelected) && $rawSelected !== '') {
            $selected = [$rawSelected];
        } else {
            $selected = [];
        }

        $label = $selected === [] ? $placeholder : implode(' · ', $selected);
        $fieldName = $name.'[]';
        $allOptions = array_values(array_unique([...$selected, ...$options]));
    } else {
        $selected = is_array($rawSelected) ? (string) ($rawSelected[0] ?? '') : (string) ($rawSelected ?? '');
        $label = $selected !== '' ? $selected : $placeholder;
        $fieldName = $name;
        $allOptions = $options;
    }
@endphp

<div class="ph-select {{ $multiple ? 'ph-select--multiple' : '' }}" data-ph-select @if($multiple) data-multiple="1" data-placeholder="{{ $placeholder }}" @endif>
    <select
        name="{{ $fieldName }}"
        class="ph-select-native"
        {{ $required ? 'required' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        tabindex="-1"
        aria-hidden="true"
    >
        @unless($multiple)
            <option value="" @if($required) disabled @endif {{ $selected === '' ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endunless
        @if(! $multiple && $selected !== '' && ! in_array($selected, $options, true))
            <option value="{{ $selected }}" selected>{{ $selected }}</option>
        @endif
        @foreach($allOptions as $option)
            <option
                value="{{ $option }}"
                @selected($multiple ? in_array($option, $selected, true) : $selected === $option)
            >{{ $option }}</option>
        @endforeach
    </select>
    <button type="button" class="form-select ph-input ph-select-toggle" aria-haspopup="listbox" aria-expanded="false">
        <span class="ph-select-label {{ ($multiple ? $selected === [] : $selected === '') ? 'is-placeholder' : '' }}">{{ $label }}</span>
    </button>
    <ul class="ph-select-menu" role="listbox" @if($multiple) aria-multiselectable="true" @endif hidden>
        @unless($multiple)
            <li role="option" data-value="" class="{{ $selected === '' ? 'is-selected' : '' }}">{{ $placeholder }}</li>
        @endunless
        @foreach($allOptions as $option)
            <li
                role="option"
                data-value="{{ $option }}"
                class="{{ ($multiple ? in_array($option, $selected, true) : $selected === $option) ? 'is-selected' : '' }}"
            >{{ $option }}</li>
        @endforeach
    </ul>
</div>

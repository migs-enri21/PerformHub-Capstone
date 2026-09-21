@props([
    'name',
    'options' => [],
    'groups' => null,
    'value' => '',
    'placeholder' => 'Select',
    'required' => false,
    'multiple' => false,
    'guidedKind' => null,
    'emptyMessage' => 'Select a category first',
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

        $label = $selected === [] ? $placeholder : (
            count($selected) <= 2
                ? implode(' · ', $selected)
                : $selected[0].' + '.(count($selected) - 1).' more'
        );
        $fieldName = $name.'[]';
    } else {
        $selected = is_array($rawSelected) ? (string) ($rawSelected[0] ?? '') : (string) ($rawSelected ?? '');
        $label = $selected !== '' ? $selected : $placeholder;
        $fieldName = $name;
    }

    $grouped = is_array($groups);
    $menuGroups = $grouped
        ? array_values(array_filter($groups, fn ($group) => is_array($group) && ! empty($group['options'])))
        : [];
    $groupedOptions = [];

    foreach ($menuGroups as $group) {
        foreach ($group['options'] as $option) {
            if (is_string($option) && $option !== '' && ! in_array($option, $groupedOptions, true)) {
                $groupedOptions[] = $option;
            }
        }
    }

    $allOptions = $grouped
        ? $groupedOptions
        : ($multiple
            ? array_values(array_unique([...$selected, ...$options]))
            : $options);
    $isEmpty = $allOptions === [];
@endphp

<div
    class="ph-select {{ $multiple ? 'ph-select--multiple' : '' }}"
    data-ph-select
    @if($multiple) data-multiple="1" data-placeholder="{{ $placeholder }}" @endif
    @if($guidedKind) data-guided-kind="{{ $guidedKind }}" @endif
    data-empty-message="{{ $emptyMessage }}"
>
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
        @if(! $multiple && $selected !== '' && ! in_array($selected, $allOptions, true))
            <option value="{{ $selected }}" selected>{{ $selected }}</option>
        @endif
        @foreach($allOptions as $option)
            <option
                value="{{ $option }}"
                @selected($multiple ? in_array($option, $selected, true) : $selected === $option)
            >{{ $option }}</option>
        @endforeach
    </select>
    <button type="button" class="form-select ph-input ph-select-toggle" aria-haspopup="listbox" aria-expanded="false" @if($multiple && $selected !== []) title="{{ implode(' · ', $selected) }}" @endif>
        <span class="ph-select-label {{ ($multiple ? $selected === [] : $selected === '') ? 'is-placeholder' : '' }}">{{ $label }}</span>
    </button>
    <ul class="ph-select-menu" role="listbox" @if($multiple) aria-multiselectable="true" @endif hidden>
        @if($isEmpty)
            <li class="ph-select-empty">{{ $emptyMessage }}</li>
        @else
            @unless($multiple)
                <li role="option" data-value="" class="{{ $selected === '' ? 'is-selected' : '' }}">{{ $placeholder }}</li>
            @endunless
            @if($grouped)
                @foreach($menuGroups as $group)
                    <li class="ph-select-group" aria-hidden="true">{{ $group['label'] }}</li>
                    @foreach($group['options'] as $option)
                        <li
                            role="option"
                            data-value="{{ $option }}"
                            class="{{ ($multiple ? in_array($option, $selected, true) : $selected === $option) ? 'is-selected' : '' }}"
                        >{{ $option }}</li>
                    @endforeach
                @endforeach
            @else
                @foreach($allOptions as $option)
                    <li
                        role="option"
                        data-value="{{ $option }}"
                        class="{{ ($multiple ? in_array($option, $selected, true) : $selected === $option) ? 'is-selected' : '' }}"
                    >{{ $option }}</li>
                @endforeach
            @endif
        @endif
    </ul>
</div>

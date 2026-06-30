@props([
    'region' => '',
    'city' => '',
    'barangay' => '',
    'required' => false,
])

@php
    use App\Support\PhilippineLocations;
    $selectedRegion = old('region', $region);
    $selectedCity = old('city', $city);
    $selectedBarangay = old('barangay', $barangay);
@endphp

<div class="ph-location-cascade"
    data-region="{{ $selectedRegion }}"
    data-city="{{ $selectedCity }}"
    data-barangay="{{ $selectedBarangay }}">

    <div class="mb-3">
        <label class="form-label text-muted small">Region</label>
        <select name="region" class="form-select ph-input ph-location-region" {{ $required ? 'required' : '' }}>
            <option value="">Select region</option>
            @foreach(PhilippineLocations::regions() as $regionName)
                <option value="{{ $regionName }}" @selected($selectedRegion === $regionName)>{{ $regionName }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">City / Municipality</label>
        <select name="city" class="form-select ph-input ph-location-city" {{ $required ? 'required' : '' }} disabled>
            <option value="">Select city / municipality</option>
        </select>
    </div>

    <div class="{{ $required ? 'mb-0' : 'mb-3' }}">
        <label class="form-label text-muted small">Barangay</label>
        <select name="barangay" class="form-select ph-input ph-location-barangay" {{ $required ? 'required' : '' }} disabled>
            <option value="">Select barangay</option>
        </select>
    </div>
</div>

@once
    @push('scripts')
        <script>
        window.phLocations = @json(\App\Support\PhilippineLocations::places());

        function initLocationCascade(root) {
            const regionSelect = root.querySelector('.ph-location-region');
            const citySelect = root.querySelector('.ph-location-city');
            const barangaySelect = root.querySelector('.ph-location-barangay');
            const data = window.phLocations || {};

            function fillSelect(select, items, placeholder, selected) {
                select.innerHTML = '';
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = placeholder;
                select.appendChild(placeholderOption);

                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = item;
                    if (item === selected) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                select.disabled = items.length === 0;
            }

            function syncCities(preserveCity = '', preserveBarangay = '') {
                const region = regionSelect.value;
                const cities = region && data[region] ? Object.keys(data[region]) : [];
                fillSelect(citySelect, cities, 'Select city / municipality', preserveCity);
                syncBarangays(preserveBarangay);
            }

            function syncBarangays(preserveBarangay = '') {
                const region = regionSelect.value;
                const city = citySelect.value;
                const barangays = region && city && data[region] && data[region][city]
                    ? data[region][city]
                    : [];
                fillSelect(barangaySelect, barangays, 'Select barangay', preserveBarangay);
            }

            regionSelect.addEventListener('change', () => {
                syncCities();
            });

            citySelect.addEventListener('change', () => {
                syncBarangays();
            });

            if (regionSelect.value) {
                syncCities(root.dataset.city || '', root.dataset.barangay || '');
            }
        }

        document.querySelectorAll('.ph-location-cascade').forEach(initLocationCascade);
        </script>
    @endpush
@endonce

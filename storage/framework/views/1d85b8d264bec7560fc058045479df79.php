<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'region' => '',
    'city' => '',
    'barangay' => '',
    'required' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'region' => '',
    'city' => '',
    'barangay' => '',
    'required' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\PhilippineLocations;
    $selectedRegion = old('region', $region);
    $selectedCity = old('city', $city);
    $selectedBarangay = old('barangay', $barangay);
?>

<div class="ph-location-cascade"
    data-region="<?php echo e($selectedRegion); ?>"
    data-city="<?php echo e($selectedCity); ?>"
    data-barangay="<?php echo e($selectedBarangay); ?>">

    <div class="mb-3">
        <label class="form-label text-muted small">Region</label>
        <select name="region" class="form-select ph-input ph-location-region" <?php echo e($required ? 'required' : ''); ?>>
            <option value="">Select region</option>
            <?php $__currentLoopData = PhilippineLocations::regions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regionName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($regionName); ?>" <?php if($selectedRegion === $regionName): echo 'selected'; endif; ?>><?php echo e($regionName); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">City / Municipality</label>
        <select name="city" class="form-select ph-input ph-location-city" <?php echo e($required ? 'required' : ''); ?> disabled>
            <option value="">Select city / municipality</option>
        </select>
    </div>

    <div class="<?php echo e($required ? 'mb-0' : 'mb-3'); ?>">
        <label class="form-label text-muted small">Barangay</label>
        <select name="barangay" class="form-select ph-input ph-location-barangay" <?php echo e($required ? 'required' : ''); ?> disabled>
            <option value="">Select barangay</option>
        </select>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('b8a36b42-4aeb-47da-8612-e28caf4acb88')): $__env->markAsRenderedOnce('b8a36b42-4aeb-47da-8612-e28caf4acb88'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
        window.phLocations = <?php echo json_encode(\App\Support\PhilippineLocations::places(), 15, 512) ?>;

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
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\location-select.blade.php ENDPATH**/ ?>
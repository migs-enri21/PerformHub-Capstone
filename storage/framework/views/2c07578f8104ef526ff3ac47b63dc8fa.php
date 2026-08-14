<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
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
    'name' => 'genre',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select genre',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\PerformerGenres;
    $selected = old($name, $value);
    $options = PerformerGenres::all();
?>

<select name="<?php echo e($name); ?>" class="form-select ph-input" <?php echo e($required ? 'required' : ''); ?>>
    <option value="" <?php if($required): ?> disabled <?php endif; ?> <?php echo e($selected ? '' : 'selected'); ?>><?php echo e($placeholder); ?></option>
    <?php if($selected && ! in_array($selected, $options, true)): ?>
        <option value="<?php echo e($selected); ?>" selected><?php echo e($selected); ?></option>
    <?php endif; ?>
    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($genre); ?>" <?php if($selected === $genre): echo 'selected'; endif; ?>><?php echo e($genre); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\genre-select.blade.php ENDPATH**/ ?>
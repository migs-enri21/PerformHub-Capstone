<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'title', 'required' => false, 'desc' => '', 'formats' => '', 'icon' => 'fa-file']));

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

foreach (array_filter((['name', 'title', 'required' => false, 'desc' => '', 'formats' => '', 'icon' => 'fa-file']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="upload-field mb-3" data-field-name="<?php echo e($name); ?>">
    <label class="upload-field-label w-100">
        <input type="file" name="<?php echo e($name); ?>" class="d-none upload-input" accept=".jpg,.jpeg,.png,.pdf<?php echo e(str_contains($formats, 'zip') ? ',.zip' : ''); ?><?php echo e(str_contains($formats, 'mp4') ? ',.mp4,.mov' : ''); ?>" <?php echo e($required ? 'required' : ''); ?> data-max-size="<?php echo e(preg_match('/max (\d+(?:\.\d+)?)\s*(MB|GB|KB)/i', $formats, $m) ? (int)($m[2] === 'GB' ? $m[1] * 1024 : ($m[2] === 'MB' ? $m[1] : $m[1] / 1024)) : 5); ?>">
        <div class="upload-field-inner d-flex align-items-start gap-3">
            <div class="upload-field-icon"><i class="fas <?php echo e($icon); ?>"></i></div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-semibold small"><?php echo e($title); ?></span>
                    <?php if($required): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size:0.65rem;">Required</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill" style="font-size:0.65rem;">Optional</span>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mb-1"><?php echo e($desc); ?></p>
                <span class="text-muted" style="font-size:0.75rem;"><?php echo e($formats); ?></span>
                <div class="upload-filename small text-success mt-2 d-none d-flex align-items-center gap-2">
                    <span class="upload-filename-text"></span>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-file" title="Delete file">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="upload-error small text-danger mt-1 d-none"></div>
            </div>
            <i class="fas fa-cloud-upload-alt text-muted"></i>
        </div>
    </label>
</div>

<?php if (! $__env->hasRenderedOnce('f5fe621a-5de5-44e6-8d35-5ae3f69ae3e5')): $__env->markAsRenderedOnce('f5fe621a-5de5-44e6-8d35-5ae3f69ae3e5'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.upload-field').forEach(field => {
    const input = field.querySelector('.upload-input');
    const nameEl = field.querySelector('.upload-filename');
    const nameTextEl = field.querySelector('.upload-filename-text');
    const errorEl = field.querySelector('.upload-error');
    const deleteBtn = field.querySelector('.delete-file');
    
    function validateFile() {
        const maxSizeMB = parseFloat(input.getAttribute('data-max-size'));
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        
        errorEl.classList.add('d-none');
        
        if (input.files.length) {
            const file = input.files[0];
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            if (file.size > maxSizeBytes) {
                errorEl.textContent = `❌ File too large! "${file.name}" is ${fileSizeMB} MB. Maximum allowed: ${maxSizeMB} MB`;
                errorEl.classList.remove('d-none');
                nameEl.classList.add('d-none');
                field.querySelector('.upload-field-inner').classList.remove('has-file');
                input.value = '';
            } else {
                nameTextEl.textContent = `✓ ${file.name} (${fileSizeMB} MB)`;
                nameEl.classList.remove('d-none');
                field.querySelector('.upload-field-inner').classList.add('has-file');
            }
        } else {
            nameEl.classList.add('d-none');
            field.querySelector('.upload-field-inner').classList.remove('has-file');
        }
    }
    
    input.addEventListener('change', validateFile);
    
    deleteBtn.addEventListener('click', (e) => {
        e.preventDefault();
        input.value = '';
        nameEl.classList.add('d-none');
        errorEl.classList.add('d-none');
        field.querySelector('.upload-field-inner').classList.remove('has-file');
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\onboarding\partials\upload-field.blade.php ENDPATH**/ ?>
@props(['name', 'title', 'required' => false, 'desc' => '', 'formats' => '', 'icon' => 'fa-file'])
<div class="upload-field mb-3">
    <label class="upload-field-label w-100">
        <input type="file" name="{{ $name }}" class="d-none upload-input" accept=".jpg,.jpeg,.png,.pdf{{ str_contains($formats, 'zip') ? ',.zip' : '' }}{{ str_contains($formats, 'mp4') ? ',.mp4,.mov' : '' }}" {{ $required ? 'required' : '' }} data-max-size="{{ preg_match('/max (\d+(?:\.\d+)?)\s*(MB|GB|KB)/i', $formats, $m) ? (int)($m[2] === 'GB' ? $m[1] * 1024 : ($m[2] === 'MB' ? $m[1] : $m[1] / 1024)) : 5 }}">
        <div class="upload-field-inner d-flex align-items-start gap-3">
            <div class="upload-field-icon"><i class="fas {{ $icon }}"></i></div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-semibold small">{{ $title }}</span>
                    @if($required)
                        <span class="badge bg-danger rounded-pill" style="font-size:0.65rem;">Required</span>
                    @else
                        <span class="badge bg-secondary rounded-pill" style="font-size:0.65rem;">Optional</span>
                    @endif
                </div>
                <p class="text-muted small mb-1">{{ $desc }}</p>
                <span class="text-muted" style="font-size:0.75rem;">{{ $formats }}</span>
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

@once
@push('scripts')
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
@endpush
@endonce

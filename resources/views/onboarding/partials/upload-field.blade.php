@props(['name', 'title', 'required' => false, 'desc' => '', 'formats' => '', 'icon' => 'fa-file'])
<div class="upload-field mb-3">
    <label class="upload-field-label w-100">
        <input type="file" name="{{ $name }}" class="d-none" accept=".jpg,.jpeg,.png,.pdf{{ str_contains($formats, 'zip') ? ',.zip' : '' }}{{ str_contains($formats, 'mp4') ? ',.mp4,.mov' : '' }}" {{ $required ? 'required' : '' }}>
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
                <div class="upload-filename small text-success mt-1 d-none"></div>
            </div>
            <i class="fas fa-cloud-upload-alt text-muted"></i>
        </div>
    </label>
</div>

@once
@push('scripts')
<script>
document.querySelectorAll('.upload-field input[type=file]').forEach(input => {
    input.addEventListener('change', () => {
        const label = input.closest('.upload-field');
        const nameEl = label.querySelector('.upload-filename');
        if (input.files.length) {
            nameEl.textContent = input.files[0].name;
            nameEl.classList.remove('d-none');
            label.querySelector('.upload-field-inner').classList.add('has-file');
        }
    });
});
</script>
@endpush
@endonce

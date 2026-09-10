@props(['name', 'title', 'required' => false, 'desc' => '', 'formats' => '', 'icon' => 'fa-file', 'multiple' => false, 'maxFiles' => 8])
@php
    $acceptParts = ['.jpg', '.jpeg', '.png'];
    if (str_contains($formats, 'pdf')) {
        $acceptParts[] = '.pdf';
    }
    if (str_contains($formats, 'zip')) {
        $acceptParts[] = '.zip';
    }
    if (str_contains($formats, 'mp4') || str_contains($formats, 'video')) {
        $acceptParts = array_merge($acceptParts, ['.webp', '.gif', '.mp4', '.mov', '.webm']);
    }
    $accept = implode(',', array_unique($acceptParts));
    $inputName = $multiple ? $name.'[]' : $name;
    $maxSizeMb = 5;
    if (preg_match('/max (\d+(?:\.\d+)?)\s*(MB|GB|KB)/i', $formats, $m)) {
        $maxSizeMb = (int) ($m[2] === 'GB' ? $m[1] * 1024 : ($m[2] === 'MB' ? $m[1] : $m[1] / 1024));
    }
@endphp
<div class="upload-field mb-3" data-field-name="{{ $name }}" data-multiple="{{ $multiple ? '1' : '0' }}" data-max-files="{{ $maxFiles }}">
    <label class="upload-field-label w-100">
        <input
            type="file"
            name="{{ $inputName }}"
            class="d-none upload-input"
            accept="{{ $accept }}"
            {{ $required ? 'required' : '' }}
            {{ $multiple ? 'multiple' : '' }}
            data-max-size="{{ $maxSizeMb }}"
        >
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
            </div>
            <i class="fas fa-cloud-upload-alt text-muted"></i>
        </div>
    </label>
    <div class="upload-filename small text-success mt-2 d-none">
        <ul class="upload-file-list mb-1 ps-0"></ul>
        <span class="upload-filename-text"></span>
        <button type="button" class="btn btn-sm btn-outline-danger delete-file" title="Clear files">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
    <div class="upload-error small text-danger mt-1 d-none"></div>
    @error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error($name.'.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

@once
@push('scripts')
<script>
document.querySelectorAll('.upload-field').forEach(field => {
    const input = field.querySelector('.upload-input');
    const nameEl = field.querySelector('.upload-filename');
    const nameTextEl = field.querySelector('.upload-filename-text');
    const listEl = field.querySelector('.upload-file-list');
    const errorEl = field.querySelector('.upload-error');
    const deleteBtn = field.querySelector('.delete-file');
    const inner = field.querySelector('.upload-field-inner');
    const isMultiple = field.dataset.multiple === '1';
    const maxFiles = parseInt(field.dataset.maxFiles || '8', 10);
    let selectedFiles = [];

    function maxBytes() {
        return parseFloat(input.getAttribute('data-max-size')) * 1024 * 1024;
    }

    function syncInput() {
        if (!isMultiple) {
            return;
        }

        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
        input.required = field.querySelector('.badge.bg-danger') !== null && selectedFiles.length === 0;
    }

    function renderMultiple() {
        errorEl.classList.add('d-none');
        listEl.innerHTML = '';

        if (selectedFiles.length === 0) {
            nameEl.classList.add('d-none');
            inner.classList.remove('has-file');
            nameTextEl.textContent = '';
            syncInput();
            return;
        }

        nameEl.classList.remove('d-none');
        inner.classList.add('has-file');
        nameTextEl.textContent = selectedFiles.length === 1
            ? '1 file selected'
            : `${selectedFiles.length} files selected`;

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('li');
            item.className = 'upload-file-item';
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            item.innerHTML = `<span>✓ ${file.name} (${sizeMb} MB)</span>`;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-link text-danger p-0 ms-2';
            removeBtn.setAttribute('aria-label', 'Remove file');
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                selectedFiles.splice(index, 1);
                renderMultiple();
            });
            item.appendChild(removeBtn);
            listEl.appendChild(item);
        });

        syncInput();
    }

    function validateFile() {
        const limit = maxBytes();
        const maxSizeMB = parseFloat(input.getAttribute('data-max-size'));

        errorEl.classList.add('d-none');

        if (isMultiple) {
            const incoming = Array.from(input.files || []);
            const merged = [...selectedFiles];

            incoming.forEach(file => {
                if (file.size > limit) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    errorEl.textContent = `"${file.name}" is ${fileSizeMB} MB. Maximum allowed: ${maxSizeMB} MB`;
                    errorEl.classList.remove('d-none');
                    return;
                }

                const duplicate = merged.some(existing =>
                    existing.name === file.name &&
                    existing.size === file.size &&
                    existing.lastModified === file.lastModified
                );

                if (!duplicate) {
                    merged.push(file);
                }
            });

            if (merged.length > maxFiles) {
                errorEl.textContent = `You can upload up to ${maxFiles} files here.`;
                errorEl.classList.remove('d-none');
                selectedFiles = merged.slice(0, maxFiles);
            } else {
                selectedFiles = merged;
            }

            renderMultiple();
            return;
        }

        if (input.files.length) {
            const file = input.files[0];
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

            if (file.size > limit) {
                errorEl.textContent = `File too large! "${file.name}" is ${fileSizeMB} MB. Maximum allowed: ${maxSizeMB} MB`;
                errorEl.classList.remove('d-none');
                nameEl.classList.add('d-none');
                inner.classList.remove('has-file');
                input.value = '';
            } else {
                nameTextEl.textContent = `✓ ${file.name} (${fileSizeMB} MB)`;
                nameEl.classList.remove('d-none');
                inner.classList.add('has-file');
            }
        } else {
            nameEl.classList.add('d-none');
            inner.classList.remove('has-file');
        }
    }

    input.addEventListener('change', validateFile);

    deleteBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        selectedFiles = [];
        input.value = '';
        nameEl.classList.add('d-none');
        errorEl.classList.add('d-none');
        inner.classList.remove('has-file');
        nameTextEl.textContent = '';
        if (listEl) {
            listEl.innerHTML = '';
        }
        if (isMultiple) {
            syncInput();
        }
    });
});
</script>
@endpush
@endonce

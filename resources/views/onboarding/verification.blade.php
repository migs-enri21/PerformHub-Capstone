@extends('onboarding.layout', ['title' => 'Verification', 'current' => 3])

@section('onboarding-content')
<div class="d-flex align-items-center justify-content-center gap-2 mb-1">
    <h2 class="fw-bold text-center mb-0">
        {{ $user->isOrganizer() ? 'Verify Your Organization' : 'Verify Your Identity' }}
    </h2>
    @if($user->isOrganizer())
        <span class="badge rounded-pill" style="background:#2563eb;">Organizer</span>
    @else
        <span class="badge rounded-pill" style="background:#6346ff;">Performer</span>
    @endif
</div>
<p class="text-muted text-center mb-4">
    @if($user->isOrganizer())
        Performers need to trust you before accepting bookings. Verify your legitimacy with official documents.
    @else
        Organizers look for verified performers. Upload your ID to build trust on the platform.
    @endif
</p>

<form method="POST" action="{{ route('onboarding.verification.store') }}" enctype="multipart/form-data">
    @csrf

    @if($user->isOrganizer())
        <label class="form-label text-muted small mb-2">Organization Type <span class="text-danger">*</span></label>
        <div class="row g-2 mb-4">
            @foreach([
                'company' => ['icon' => 'fa-building', 'label' => 'Company / Corp.'],
                'individual' => ['icon' => 'fa-user', 'label' => 'Individual / Solo'],
                'nonprofit' => ['icon' => 'fa-globe', 'label' => 'Non-Profit / NGO'],
            ] as $type => $item)
                <div class="col-4">
                    <label class="org-type-card {{ old('organization_type', $user->organizerProfile?->organization_type) === $type ? 'active' : '' }}">
                        <input type="radio" name="organization_type" value="{{ $type }}" class="d-none"
                            {{ old('organization_type', $user->organizerProfile?->organization_type) === $type ? 'checked' : '' }} required>
                        <i class="fas {{ $item['icon'] }} d-block mb-2"></i>
                        <span class="small">{{ $item['label'] }}</span>
                    </label>
                </div>
            @endforeach
        </div>

        <p class="text-muted small mb-3">Upload Documents <span class="text-muted">(Required items marked)</span></p>

        @php
            $businessPermitRequired = old('organization_type', $user->organizerProfile?->organization_type) !== 'individual';
        @endphp

        @php
            $governmentIdType = old('government_id_type', '');
        @endphp

        <div class="mb-3">
            <label class="form-label text-muted small mb-2" for="government_id_type">Government ID Type <span class="text-danger">*</span></label>
            <select name="government_id_type" id="government_id_type" class="form-select ph-input @error('government_id_type') is-invalid @enderror" required>
                <option value="" disabled {{ $governmentIdType === '' ? 'selected' : '' }}>Select Government ID Type</option>
                @foreach([
                    'PhilSys National ID' => 'PhilSys National ID',
                    'Passport' => 'Passport',
                    'Driver\'s License' => 'Driver\'s License',
                    'UMID' => 'UMID',
                    'PhilHealth ID' => 'PhilHealth ID',
                    'Postal ID' => 'Postal ID',
                    'PRC ID' => 'PRC ID',
                    'SSS ID' => 'SSS ID',
                    'GSIS eCard' => 'GSIS eCard',
                    'OWWA ID' => 'OWWA ID',
                    'NBI Clearance' => 'NBI Clearance',
                    'Police Clearance' => 'Police Clearance',
                    'PWD ID' => 'PWD ID',
                    'Senior Citizen ID' => 'Senior Citizen ID',
                    'Solo Parent ID' => 'Solo Parent ID',
                    'Other Government-Issued ID' => 'Other Government-Issued ID',
                ] as $value => $label)
                    <option value="{{ $value }}" {{ $governmentIdType === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('government_id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" id="government_id_other_group" style="display: {{ $governmentIdType === 'Other Government-Issued ID' ? 'block' : 'none' }};">
            <label class="form-label text-muted small mb-2" for="government_id_other">Specify Other Government ID</label>
            <input type="text" name="government_id_other" id="government_id_other" class="form-control ph-input @error('government_id_other') is-invalid @enderror" value="{{ old('government_id_other') }}" placeholder="Enter government ID type">
            @error('government_id_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @include('onboarding.partials.upload-field', [
            'name' => 'government_id',
            'title' => 'Government-Issued ID',
            'required' => true,
            'desc' => 'Valid Philippine ID of the authorized representative.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-id-card',
        ])
        @include('onboarding.partials.upload-field', [
            'name' => 'business_permit',
            'title' => 'Business / Organization Permit',
            'required' => $businessPermitRequired,
            'desc' => 'DTI Certificate, SEC Registration, Mayor\'s Permit, or equivalent.',
            'formats' => '.jpg .png .pdf — max 10 MB',
            'icon' => 'fa-file-alt',
        ])
        @include('onboarding.partials.upload-field', [
            'name' => 'proof_of_events',
            'title' => 'Proof of Previous Events',
            'required' => false,
            'desc' => 'Event photos, contracts, or letters confirming past event experience.',
            'formats' => '.jpg .png .pdf .zip — max 50 MB',
            'icon' => 'fa-camera',
        ])
        @include('onboarding.partials.upload-field', [
            'name' => 'bir_certificate',
            'title' => 'BIR Certificate of Registration',
            'required' => false,
            'desc' => 'If your organization issues official receipts.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-certificate',
        ])
    @else
        @php
            $governmentIdType = old('government_id_type', '');
        @endphp

        <div class="mb-3">
            <label class="form-label text-muted small mb-2" for="government_id_type">Government ID Type <span class="text-danger">*</span></label>
            <select name="government_id_type" id="government_id_type" class="form-select ph-input @error('government_id_type') is-invalid @enderror" required>
                <option value="" disabled {{ $governmentIdType === '' ? 'selected' : '' }}>Select Government ID Type</option>
                @foreach([
                    'PhilSys National ID' => 'PhilSys National ID',
                    'Passport' => 'Passport',
                    'Driver\'s License' => 'Driver\'s License',
                    'UMID' => 'UMID',
                    'PhilHealth ID' => 'PhilHealth ID',
                    'Postal ID' => 'Postal ID',
                    'PRC ID' => 'PRC ID',
                    'SSS ID' => 'SSS ID',
                    'GSIS eCard' => 'GSIS eCard',
                    'OWWA ID' => 'OWWA ID',
                    'NBI Clearance' => 'NBI Clearance',
                    'Police Clearance' => 'Police Clearance',
                    'PWD ID' => 'PWD ID',
                    'Senior Citizen ID' => 'Senior Citizen ID',
                    'Solo Parent ID' => 'Solo Parent ID',
                    'Other Government-Issued ID' => 'Other Government-Issued ID',
                ] as $value => $label)
                    <option value="{{ $value }}" {{ $governmentIdType === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('government_id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" id="government_id_other_group" style="display: {{ $governmentIdType === 'Other Government-Issued ID' ? 'block' : 'none' }};">
            <label class="form-label text-muted small mb-2" for="government_id_other">Specify Other Government ID</label>
            <input type="text" name="government_id_other" id="government_id_other" class="form-control ph-input @error('government_id_other') is-invalid @enderror" value="{{ old('government_id_other') }}" placeholder="Enter government ID type">
            @error('government_id_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @include('onboarding.partials.upload-field', [
            'name' => 'government_id',
            'title' => 'Government-Issued ID',
            'required' => true,
            'desc' => 'Valid Philippine ID for identity verification.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-id-card',
        ])
        @include('onboarding.partials.upload-field', [
            'name' => 'performance_sample',
            'title' => 'Performance Sample',
            'required' => false,
            'desc' => 'Photo, video, or portfolio sample showcasing your talent.',
            'formats' => '.jpg .png .pdf .mp4 — max 500 MB',
            'icon' => 'fa-video',
        ])
    @endif

    <div class="d-flex gap-2">
        <a href="{{ route('onboarding.profile') }}" class="btn ph-btn-outline">Back</a>
        <button type="submit" class="btn ph-btn-primary flex-grow-1">
            Submit for Verification <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function updateDocumentRequirements() {
    const selectedType = document.querySelector('.org-type-card input[type=radio]:checked')?.value;
    const businessPermitField = document.querySelector('.upload-field[data-field-name="business_permit"]');

    if (!businessPermitField) {
        return;
    }

    const badge = businessPermitField.querySelector('.upload-field-label .badge');
    const input = businessPermitField.querySelector('input.upload-input');

    if (selectedType === 'individual') {
        input.removeAttribute('required');
        if (badge) {
            badge.textContent = 'Optional';
            badge.classList.remove('bg-danger');
            badge.classList.add('bg-secondary');
        }
    } else {
        input.required = true;
        if (badge) {
            badge.textContent = 'Required';
            badge.classList.remove('bg-secondary');
            badge.classList.add('bg-danger');
        }
    }
}

document.querySelectorAll('.org-type-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.org-type-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        card.querySelector('input[type=radio]').checked = true;
        updateDocumentRequirements();
    });
});

    const governmentIdTypeSelect = document.getElementById('government_id_type');
    const governmentIdOtherGroup = document.getElementById('government_id_other_group');

    if (governmentIdTypeSelect) {
        governmentIdTypeSelect.addEventListener('change', () => {
            if (governmentIdTypeSelect.value === 'Other Government-Issued ID') {
                governmentIdOtherGroup.style.display = 'block';
                document.getElementById('government_id_other').required = true;
            } else {
                governmentIdOtherGroup.style.display = 'none';
                document.getElementById('government_id_other').required = false;
            }
        });
    }

    updateDocumentRequirements();
</script>
@endpush


<div class="modal fade" id="organizerConfirmModal" tabindex="-1" aria-labelledby="organizerConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="organizerConfirmTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="organizerConfirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn ph-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn ph-btn-primary" id="organizerConfirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('organizerConfirmModal');
    const title = document.getElementById('organizerConfirmTitle');
    const message = document.getElementById('organizerConfirmMessage');
    const confirmButton = document.getElementById('organizerConfirmButton');
    let selectedForm = null;

    if (! modalElement || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);

    document.querySelectorAll('.organizer-confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            selectedForm = form;
            title.textContent = form.dataset.confirmTitle;
            message.textContent = form.dataset.confirmMessage;
            confirmButton.textContent = form.dataset.confirmButton;
            modal.show();
        });
    });

    confirmButton.addEventListener('click', function () {
        if (selectedForm) {
            selectedForm.submit();
        }
    });
});
</script>
@endpush

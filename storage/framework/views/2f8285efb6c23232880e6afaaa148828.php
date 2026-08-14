<?php $__env->startSection('title', 'Register'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="<?php echo e(route('home')); ?>" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Join the stage! </h1>
            <p class="text-white-50 fs-5">Create your account and start connecting today.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="<?php echo e(route('home')); ?>" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </a>

            <h2 class="fw-bold mb-1">Create your account</h2>
            <p class="text-muted mb-4">Choose your role and get started</p>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger py-2 mb-3">
                    <ul class="mb-0 small ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
                $showRegistration = $errors->any() || old('terms_accepted');
            ?>

            <form method="POST" action="<?php echo e(route('register')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="role" id="roleInput" value="<?php echo e(old('role', $role)); ?>">

                <div class="ph-card p-3 mb-4" style="<?php echo e($showRegistration ? 'display:none;' : ''); ?>">
                    <h5 class="fw-semibold mb-3">Terms & Conditions</h5>
                    <div class="border rounded p-3 mb-3" style="max-height: 280px; overflow-y: auto; background-color: rgba(255,255,255,.03);">
                        <p><strong>Last Updated:</strong> July 27, 2026</p>
                        <h6 class="fw-semibold">1. Acceptance of Terms</h6>
                        <p>Welcome to <strong>PerformHub</strong>. By creating an account and using our platform, you agree to comply with these Terms & Conditions. If you do not agree with these terms, you should not proceed with registration or use the platform.</p>
                        <hr class="my-3">
                        <h6 class="fw-semibold">2. User Responsibilities</h6>
                        <p>All users are responsible for providing accurate and complete information during registration and for keeping their account credentials secure. Users must use the platform responsibly and comply with all applicable laws and regulations.</p>
                        <hr class="my-3">
                        <h6 class="fw-semibold">3. Organizer Responsibilities</h6>
                        <ul>
                            <li>Create accurate and complete event listings.</li>
                            <li>Clearly communicate event details, schedules, and requirements.</li>
                            <li>Treat performers professionally and respectfully.</li>
                            <li>Honor confirmed bookings whenever possible.</li>
                            <li>Comply with all applicable laws and event regulations.</li>
                        </ul>
                        <hr class="my-3">
                        <h6 class="fw-semibold">4. Performer Responsibilities</h6>
                        <ul>
                            <li>Provide truthful profile information and qualifications.</li>
                            <li>Respond to booking requests professionally.</li>
                            <li>Attend and fulfill confirmed bookings.</li>
                            <li>Inform organizers immediately if they are unable to fulfill a commitment.</li>
                            <li>Maintain professionalism throughout all interactions.</li>
                        </ul>
                        <hr class="my-3">
                        <h6 class="fw-semibold">5. Prohibited Activities</h6>
                        <ul>
                            <li>Provide false or misleading information.</li>
                            <li>Impersonate another individual or organization.</li>
                            <li>Harass, threaten, or abuse other users.</li>
                            <li>Use the platform for fraudulent or illegal activities.</li>
                            <li>Upload malicious or inappropriate content.</li>
                            <li>Interfere with the normal operation of the platform.</li>
                        </ul>
                        <hr class="my-3">
                        <h6 class="fw-semibold">6. Account Suspension</h6>
                        <p>PerformHub reserves the right to suspend or permanently ban any account that violates these Terms & Conditions or engages in activities that may compromise the safety, integrity, or functionality of the platform.</p>
                        <hr class="my-3">
                        <h6 class="fw-semibold">7. Changes to These Terms</h6>
                        <p>PerformHub may update these Terms & Conditions at any time. Continued use of the platform after any updates constitutes acceptance of the revised Terms & Conditions.</p>
                        <hr class="my-3">
                        <h5 class="fw-semibold">Disclaimer</h5>
                        <p><strong>Last Updated:</strong> July 27, 2026</p>
                        <p>PerformHub is an online platform that connects <strong>Organizers</strong> and <strong>Performers</strong> for event bookings and collaborations.</p>
                        <p>PerformHub acts solely as a facilitator and is not a party to any agreement, contract, payment arrangement, or transaction between users.</p>
                        <p>While we strive to provide a reliable and secure platform, PerformHub does not guarantee:</p>
                        <ul>
                            <li>The quality or success of any performance or event.</li>
                            <li>The accuracy of information provided by users.</li>
                            <li>The availability or reliability of organizers or performers.</li>
                            <li>The outcome of bookings, negotiations, or collaborations.</li>
                        </ul>
                        <p>All bookings, communications, payments, and agreements are made directly between the Organizer and the Performer.</p>
                        <p>PerformHub shall not be held liable for:</p>
                        <ul>
                            <li>Event cancellations.</li>
                            <li>Payment disputes.</li>
                            <li>Scheduling conflicts.</li>
                            <li>Financial losses.</li>
                            <li>Property damage.</li>
                            <li>Personal injury.</li>
                            <li>Any dispute arising between users.</li>
                        </ul>
                        <h6 class="fw-semibold">Data Privacy</h6>
                        <p>PerformHub is committed to protecting the privacy and personal information of its users in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Philippines and its applicable rules and regulations.</p>
                        <p>By using PerformHub, users acknowledge and consent to the collection, processing, storage, and use of their personal information solely for legitimate platform purposes, including but not limited to:</p>
                        <ul>
                            <li>User registration and authentication.</li>
                            <li>Profile management.</li>
                            <li>Booking and event coordination.</li>
                            <li>Communication between Organizers and Performers.</li>
                            <li>Platform security, fraud prevention, and customer support.</li>
                        </ul>
                        <p>PerformHub implements reasonable administrative, technical, and physical security measures to safeguard users' personal information against unauthorized access, disclosure, alteration, loss, or misuse.</p>
                        <p>Personal information will not be sold or disclosed to third parties except:</p>
                        <ul>
                            <li>With the user's consent.</li>
                            <li>When required by law, court order, or lawful government request.</li>
                            <li>When necessary to protect the rights, safety, or security of PerformHub and its users.</li>
                        </ul>
                        <p>Users have the rights provided under the Data Privacy Act of 2012, including the right to be informed, access, correct, object to processing where applicable, request deletion or blocking of personal information when legally appropriate, and file a complaint with the appropriate government authority if they believe their privacy rights have been violated.</p>
                        <p>Users are encouraged to verify all event details, communicate clearly, and exercise good judgment before entering into any agreement.</p>
                        <p>By using PerformHub, you acknowledge that you have read, understood, and agreed to these Terms & Conditions, this Disclaimer, and the processing of your personal information in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>.</p>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input <?php $__errorArgs = ['terms_accepted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" <?php echo e(old('terms_accepted') ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="termsAccepted">
                            I have read and agree to the Terms & Conditions and Disclaimer.
                        </label>
                    </div>
                    <?php $__errorArgs = ['terms_accepted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <button type="button" id="continueRegistration" class="btn btn-outline-primary w-100 mt-3" disabled>Continue to Registration</button>
                </div>

                <div id="registrationFields" style="<?php echo e($showRegistration ? '' : 'display:none;'); ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">First Name</label>
                            <input type="text" name="first_name" class="form-control ph-input" value="<?php echo e(old('first_name')); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Last Name</label>
                            <input type="text" name="last_name" class="form-control ph-input" value="<?php echo e(old('last_name')); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Username <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="username" class="form-control ph-input <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('username')); ?>" autocomplete="username" placeholder="Leave blank to auto-generate from your name">
                        <div class="form-text text-muted">Letters and numbers only. Spaces become underscores (e.g. wency malinao → wency_malinao).</div>
                        <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Email Address</label>
                        <input type="email" name="email" class="form-control ph-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email')); ?>" required autocomplete="email">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Password</label>
                        <input type="password" name="password" class="form-control ph-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required autocomplete="new-password">
                        <div class="form-text text-muted">At least 8 characters.</div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control ph-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required autocomplete="new-password">
                    </div>

                    <label class="form-label text-muted small mb-2">Register as</label>
                    <div class="row g-2 mb-4">
                        <?php $__currentLoopData = ['performer' => ['icon' => 'fa-microphone', 'label' => 'Performer'], 'organizer' => ['icon' => 'fa-building', 'label' => 'Organizer']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6">
                                <div class="role-card <?php echo e(old('role', $role) === $key ? 'active' : ''); ?>" data-role="<?php echo e($key); ?>">
                                    <i class="fas <?php echo e($item['icon']); ?>"></i>
                                    <span class="small fw-semibold d-block"><?php echo e($item['label']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <button type="submit" id="registerSubmitButton" class="btn ph-btn-primary w-100 mb-3" disabled>
                        Create Account <i class="fas fa-arrow-right ms-2"></i>
                    </button>

                    <p class="text-center text-muted small mb-0">
                        Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('roleInput').value = card.dataset.role;
    });
});

const termsCheckbox = document.getElementById('termsAccepted');
const continueButton = document.getElementById('continueRegistration');
const registrationFields = document.getElementById('registrationFields');
const submitButton = document.getElementById('registerSubmitButton');

function updateContinueState() {
    continueButton.disabled = !termsCheckbox.checked;
    if (registrationFields.style.display !== 'none' && termsCheckbox.checked) {
        submitButton.disabled = false;
    }
}

if (termsCheckbox) {
    termsCheckbox.addEventListener('change', updateContinueState);
    updateContinueState();
}

if (continueButton) {
    continueButton.addEventListener('click', () => {
        if (termsCheckbox.checked) {
            registrationFields.style.display = 'block';
            continueButton.closest('.ph-card').style.display = 'none';
            submitButton.disabled = false;
        }
    });
}

if (registrationFields && registrationFields.style.display !== 'none' && termsCheckbox && termsCheckbox.checked) {
    submitButton.disabled = false;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\auth\register.blade.php ENDPATH**/ ?>
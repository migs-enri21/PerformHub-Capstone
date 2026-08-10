<?php $__env->startSection('title', 'PerformHub - Connect Performers & Event Organizers'); ?>

<?php $__env->startSection('content'); ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-ph fixed-top" style="z-index: 1030;">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo e(route('home')); ?>"><img src="<?php echo e(asset('images/logo.png')); ?>" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLanding">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navLanding">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <?php if(auth()->guard()->guest()): ?>
                    <li class="nav-item"><a class="btn ph-btn-outline btn-sm" href="<?php echo e(route('login')); ?>">Sign In</a></li>
                    <li class="nav-item"><a class="btn ph-btn-primary btn-sm" href="<?php echo e(route('register')); ?>">Get Started</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn ph-btn-primary btn-sm" href="<?php echo e(auth()->user()->dashboardRoute()); ?>">Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-landing text-white">
    <div class="container pt-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold mb-4">Discover Talent.<br>Book the Perfect Performance.</h1>
                <p class="lead text-white-50 mb-4">PerformHub connects talented performers with event organizers for seamless entertainment booking, auditions, and event coordination.</p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if(auth()->guard()->guest()): ?>
                        <a href="<?php echo e(route('register', ['role' => 'organizer'])); ?>" class="btn ph-btn-primary btn-lg">Find Performers</a>
                        <a href="<?php echo e(route('register', ['role' => 'performer'])); ?>" class="btn ph-btn-outline btn-lg text-white">Join as Performer</a>
                    <?php else: ?>
                        <a href="<?php echo e(auth()->user()->dashboardRoute()); ?>" class="btn ph-btn-primary btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="categories" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Browse by Category</h2>
            <p class="text-muted">Find performers across every entertainment genre</p>
        </div>
        <div class="row g-4">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="ph-card p-4 text-center h-100">
                        <div class="category-icon"><i class="fas <?php echo e($category->icon ?? 'fa-star'); ?>"></i></div>
                        <h6 class="fw-semibold"><?php echo e($category->name); ?></h6>
                        <p class="text-muted small mb-0"><?php echo e(Str::limit($category->description, 60)); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12 text-center text-muted">Categories coming soon.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--ph-bg-card);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Performers</h2>
            <p class="text-muted">Top talent ready for your next event</p>
        </div>
        <div class="row g-4">
            <?php $__empty_1 = true; $__currentLoopData = $featuredPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="ph-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?php echo e($performer->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff'); ?>" class="performer-avatar" alt="">
                            <div>
                                <h6 class="mb-0 fw-semibold">
                                    <?php echo e($performer->stage_name); ?>

                                    <?php if($performer->is_verified_badge): ?><i class="fas fa-circle-check verified-badge ms-1"></i><?php endif; ?>
                                </h6>
                                <small class="text-muted"><?php echo e($performer->categoryNames() ?: 'Performer'); ?> · <?php echo e($performer->location ?? 'Philippines'); ?></small>
                            </div>
                        </div>
                        <p class="text-muted small"><?php echo e(Str::limit($performer->bio, 100) ?: 'Talented performer available for bookings.'); ?></p>
                        <?php if($performer->rate): ?><p class="mb-0 fw-semibold text-primary">₱<?php echo e(number_format($performer->rate, 2)); ?>/event</p><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12 text-center text-muted">No featured performers yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How PerformHub Works</h2>
            <p class="text-muted">Book talent in three simple steps</p>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = [['icon'=>'fa-search','title'=>'Discover','desc'=>'Search and filter performers by category, genre, and availability.'],['icon'=>'fa-paper-plane','title'=>'Request','desc'=>'Send booking requests with event details and requirements.'],['icon'=>'fa-check-circle','title'=>'Book','desc'=>'Accept bookings, manage contracts, and complete events.']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-3">
                    <div class="ph-card p-4 text-center h-100">
                        <div class="category-icon"><i class="fas <?php echo e($step['icon']); ?>"></i></div>
                        <h6 class="fw-semibold"><?php echo e($step['title']); ?></h6>
                        <p class="text-muted small mb-0"><?php echo e($step['desc']); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose PerformHub</h2>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = [['icon'=>'fa-shield-halved','title'=>'Verified Performers','desc'=>'Admin-verified profiles with badge system.'],['icon'=>'fa-calendar-check','title'=>'Smart Scheduling','desc'=>'Availability calendars synced with Google Calendar.'],['icon'=>'fa-file-contract','title'=>'Contract Management','desc'=>'Upload and confirm contracts digitally.']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-3">
                    <div class="ph-card p-4 h-100">
                        <i class="fas <?php echo e($f['icon']); ?> text-primary fs-4 mb-3"></i>
                        <h6 class="fw-semibold"><?php echo e($f['title']); ?></h6>
                        <p class="text-muted small mb-0"><?php echo e($f['desc']); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-section p-5 text-center text-white">
            <h2 class="fw-bold mb-3">Ready to take the stage?</h2>
            <p class="mb-4 opacity-75">Join thousands of performers and organizers on PerformHub today.</p>
            <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('register')); ?>" class="btn btn-light btn-lg fw-semibold px-5">Get Started Free</a>
            <?php else: ?>
                <a href="<?php echo e(auth()->user()->dashboardRoute()); ?>" class="btn btn-light btn-lg fw-semibold px-5">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="py-4 border-top" style="border-color: var(--ph-border) !important;">
    <div class="container text-center text-muted small">
        <p class="mb-1"><a href="<?php echo e(route('terms')); ?>" class="text-muted text-decoration-none">Terms & Agreement</a></p>
        <p class="mb-0">&copy; <?php echo e(date('Y')); ?> PerformHub. All rights reserved.</p>
    </div>
</footer>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/landing.blade.php ENDPATH**/ ?>
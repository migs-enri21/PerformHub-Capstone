<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - PerformHub</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <?php echo $__env->make('partials.stylesheets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ph sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo e(route('home')); ?>">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('notifications.index')); ?>">
                        <i class="fas fa-bell fs-4"></i>
                            <?php if(auth()->user()->notifications()->where('is_read', false)->count()): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo e(auth()->user()->notifications()->where('is_read', false)->count()); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
        <aside class="col-lg-2 d-none d-lg-flex flex-column sidebar-ph p-3">
                <?php echo $__env->yieldContent('sidebar'); ?>
            </aside>
            <main class="col-lg-10 p-4">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show ph-autodismiss-alert" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if(session('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show ph-autodismiss-alert" role="alert">
                        <?php echo e(session('warning')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show ph-autodismiss-alert" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                    </div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('js/portfolio-autoplay.js')); ?>"></script>
    <script>
        document.querySelectorAll('.ph-autodismiss-alert').forEach(function (alertEl) {
            setTimeout(function () {
                var alert = bootstrap.Alert.getOrCreateInstance(alertEl);
                alert.close();
            }, 5000);
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\layouts\app.blade.php ENDPATH**/ ?>
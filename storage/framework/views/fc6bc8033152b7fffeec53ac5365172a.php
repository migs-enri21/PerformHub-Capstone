<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items',
    'editable' => false,
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
    'items',
    'editable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $count = $items->count();
    $layout = match (true) {
        $count === 1 => 'portfolio-collage--1',
        $count === 2 => 'portfolio-collage--2',
        $count === 3 => 'portfolio-collage--3',
        $count === 4 => 'portfolio-collage--4',
        default => 'portfolio-collage--many',
    };
    $visible = $count > 4 ? $items->take(4) : $items;
    $caption = $items->first()->caption;
    $hasMore = $count > 4;
    $modalId = 'portfolio-gallery-'.$items->first()->id;
    $editModalId = 'portfolio-edit-'.$items->first()->id;
?>

<article class="portfolio-feed-card">
    <div
        class="portfolio-preview-collage portfolio-feed-collage <?php echo e($layout); ?>"
        <?php if($hasMore): ?>
            role="button"
            tabindex="0"
            data-bs-toggle="modal"
            data-bs-target="#<?php echo e($modalId); ?>"
            aria-label="View all <?php echo e($count); ?> items"
        <?php endif; ?>
    >
        <?php $__currentLoopData = $visible; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="portfolio-collage-tile">
                <?php if($item->type === 'photo'): ?>
                    <img src="<?php echo e($item->fileUrl()); ?>" alt="<?php echo e($caption ?? ''); ?>">
                <?php else: ?>
                    <video src="<?php echo e($item->fileUrl()); ?>" <?php if(!$hasMore): ?> controls <?php endif; ?> playsinline></video>
                    <span class="portfolio-collage-badge"><i class="fas fa-play me-1"></i>Video</span>
                <?php endif; ?>
                <?php if($hasMore && $index === 3): ?>
                    <div class="portfolio-collage-more">+<?php echo e($count - 4); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($hasMore): ?>
        <div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">All <?php echo e($count); ?> items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="portfolio-gallery-grid">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="portfolio-gallery-item">
                                    <?php if($item->type === 'photo'): ?>
                                        <img src="<?php echo e($item->fileUrl()); ?>" alt="<?php echo e($caption ?? ''); ?>">
                                    <?php else: ?>
                                        <video src="<?php echo e($item->fileUrl()); ?>" controls playsinline></video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($caption || $editable): ?>
        <div class="portfolio-feed-footer px-3 py-3">
            <?php if($caption): ?>
                <p class="mb-0 small"><?php echo e($caption); ?></p>
            <?php endif; ?>
            <?php if($editable): ?>
                <div class="<?php echo e($caption ? 'mt-2' : ''); ?>">
                    <button type="button" class="btn btn-sm ph-btn-outline" data-bs-toggle="modal" data-bs-target="#<?php echo e($editModalId); ?>">
                        <i class="fas fa-pen me-1"></i> Edit
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($editable): ?>
        <div class="modal fade" id="<?php echo e($editModalId); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="<?php echo e(route('performer.portfolio.update')); ?>" method="POST" enctype="multipart/form-data" class="portfolio-edit-form">
                        <?php echo csrf_field(); ?>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="item_ids[]" value="<?php echo e($item->id); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="modal-header">
                            <h5 class="modal-title">Edit post</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label text-muted small" for="portfolioEditCaption-<?php echo e($items->first()->id); ?>">Caption</label>
                            <textarea name="caption" id="portfolioEditCaption-<?php echo e($items->first()->id); ?>" class="form-control ph-input mb-3" rows="3" maxlength="2000"><?php echo e($caption); ?></textarea>

                            <label class="form-label text-muted small">Current photos/videos</label>
                            <p class="text-muted small mb-2">Click the <i class="fas fa-times"></i> on an item to remove it from this post.</p>
                            <div class="portfolio-edit-grid mb-3">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="portfolio-edit-tile" data-item-id="<?php echo e($item->id); ?>">
                                        <?php if($item->type === 'photo'): ?>
                                            <img src="<?php echo e($item->fileUrl()); ?>" alt="">
                                        <?php else: ?>
                                            <video src="<?php echo e($item->fileUrl()); ?>" muted playsinline></video>
                                        <?php endif; ?>
                                        <button type="button" class="portfolio-edit-tile-remove" aria-label="Remove this item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <label for="portfolioEditFiles-<?php echo e($items->first()->id); ?>" class="form-label text-muted small">Add more photos or videos</label>
                            <input
                                type="file"
                                name="files[]"
                                id="portfolioEditFiles-<?php echo e($items->first()->id); ?>"
                                class="form-control ph-input"
                                accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/*"
                                multiple
                            >
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn ph-btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn ph-btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</article>

<?php if (! $__env->hasRenderedOnce('7ee9569e-ba38-491f-8b3e-bdb9e135734b')): $__env->markAsRenderedOnce('7ee9569e-ba38-491f-8b3e-bdb9e135734b'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
        document.addEventListener('hidden.bs.modal', (e) => {
            e.target.querySelectorAll('video').forEach(video => video.pause());
        });

        document.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.portfolio-edit-tile-remove');
            if (!removeBtn) return;

            const tile = removeBtn.closest('.portfolio-edit-tile');
            const form = removeBtn.closest('form');
            const itemId = tile.dataset.itemId;
            const existing = form.querySelector(`input[name="remove_ids[]"][value="${itemId}"]`);

            if (existing) {
                existing.remove();
                tile.classList.remove('portfolio-edit-tile--removed');
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_ids[]';
                hidden.value = itemId;
                form.appendChild(hidden);
                tile.classList.add('portfolio-edit-tile--removed');
                removeBtn.innerHTML = '<i class="fas fa-undo"></i>';
            }
        });

        document.addEventListener('submit', (e) => {
            if (!e.target.matches('.portfolio-edit-form')) return;
            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Saving…';
            }
        });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/partials/portfolio-collage.blade.php ENDPATH**/ ?>
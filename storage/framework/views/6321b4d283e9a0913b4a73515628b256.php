<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">Admin Dashboard</h2>
<div class="row g-4 mb-4">
    <?php $__currentLoopData = [['label'=>'Total Users','value'=>$stats['users']],['label'=>'Performers','value'=>$stats['performers']],['label'=>'Organizers','value'=>$stats['organizers']],['label'=>'Bookings','value'=>$stats['bookings']],['label'=>'Pending Verifications','value'=>$stats['pending_verifications']]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 col-lg-2">
            <div class="ph-card p-3 stat-card text-center">
                <h4 class="fw-bold mb-0"><?php echo e($stat['value']); ?></h4>
                <small class="text-muted"><?php echo e($stat['label']); ?></small>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="ph-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Recent Bookings</h5>
        
    </div>

  
        </form> 
    </div>

    <table class="table table-dark table-sm mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Performer</th><th>Status</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $recentBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($b->event_name); ?></td>
                    <td><?php echo e($b->organizer->name); ?></td>
                    <td><?php echo e($b->performer->name); ?></td>
                    <td><span class="badge <?php echo e($b->statusBadgeClass()); ?>"><?php echo e($b->statusLabel()); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
    (function(){
        const toggleBtn = document.getElementById('dashboardToggleFilters');
        const resetBtn = document.getElementById('dashboardResetFilters');
        const filters = document.getElementById('dashboardFilters');
        const applyBtn = document.getElementById('dashboardApplyFilters');
        const searchInput = document.getElementById('dashboardSearch');
        const statusSelect = document.getElementById('dashboardStatus');
        const organizerSelect = document.getElementById('dashboardOrganizer');
        const table = document.querySelector('.ph-card table tbody');

        if(toggleBtn) toggleBtn.addEventListener('click', () => {
            filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
        });

        function resetFilters(){
            if(searchInput) searchInput.value = '';
            if(statusSelect) statusSelect.value = '';
            if(organizerSelect) organizerSelect.value = '';
            filterRows();
        }

        if(resetBtn) resetBtn.addEventListener('click', resetFilters);
        if(applyBtn) applyBtn.addEventListener('click', filterRows);

        if(searchInput) searchInput.addEventListener('input', filterRows);
        if(statusSelect) statusSelect.addEventListener('change', filterRows);
        if(organizerSelect) organizerSelect.addEventListener('change', filterRows);

        function filterRows(){
            const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const status = statusSelect ? statusSelect.value.toLowerCase() : '';
            const organizer = organizerSelect ? organizerSelect.value.toLowerCase() : '';

            table.querySelectorAll('tr').forEach(tr => {
                const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim().toLowerCase());
                const matchesQuery = q === '' || cols.some(c => c.includes(q));
                const matchesStatus = status === '' || cols.some(c => c.includes(status));
                const matchesOrganizer = organizer === '' || cols.some(c => c.includes(organizer));
                tr.style.display = (matchesQuery && matchesStatus && matchesOrganizer) ? '' : 'none';
            });
        }
        // initial filter pass so table reflects any inputs on load
        filterRows();
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>
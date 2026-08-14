<?php $__env->startSection('title', 'Organizer Calendar'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Calendar</h2>
        <p class="text-muted mb-0">View your events and schedule in one place.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="org-panel">
            <div
                class="availability-calendar organizer-calendar"
                data-events='<?php echo json_encode($calendarEvents, 15, 512) ?>'
                data-google-busy='<?php echo json_encode($googleBusy, 15, 512) ?>'
            >
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="prev" aria-label="Previous month">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h5 class="fw-semibold mb-0 organizer-calendar-month"></h5>
                        <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="next" aria-label="Next month">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm ph-btn-outline organizer-calendar-nav" data-action="today">Today</button>
                </div>

                <div class="availability-calendar-weekdays">
                    <?php $__currentLoopData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><?php echo e($weekday); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="availability-calendar-grid" role="grid" aria-label="Organizer event calendar"></div>

                <div class="availability-calendar-legend mt-3">
                    <span><i class="av-legend-dot av-legend-dot--booked"></i> Your event</span>
                    <span><i class="av-legend-dot av-legend-dot--google"></i> Busy on Google Calendar</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="org-right-column">
            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Google Calendar</h6>

                    <?php if($profile->google_calendar_connected): ?>
                        <span class="badge text-bg-success">Connected</span>
                    <?php endif; ?>
                </div>

                <?php if($profile->google_calendar_connected): ?>
                    <p class="text-muted small">
                        Busy dates from Google Calendar are shown in gray.
                        <?php if($profile->google_calendar_synced_at): ?>
                            Last synced <?php echo e($profile->google_calendar_synced_at->diffForHumans()); ?>.
                        <?php endif; ?>
                    </p>

                    <form method="POST" action="<?php echo e(route('organizer.calendar.sync')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-sm ph-btn-primary">Sync Calendar</button>
                    </form>

                    <form method="POST" action="<?php echo e(route('organizer.calendar.disconnect')); ?>" class="d-inline ms-1">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted small">Connect Google Calendar to show your external busy dates here.</p>
                    <a href="<?php echo e(route('organizer.calendar.connect')); ?>" class="btn btn-sm ph-btn-outline">
                        <i class="fab fa-google me-1"></i>Connect Google Calendar
                    </a>
                <?php endif; ?>
            </div>

            <div class="org-panel">
                <h6 class="fw-bold mb-3">Upcoming Events</h6>

                <?php $__empty_1 = true; $__currentLoopData = $upcomingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('organizer.events.show', $event)); ?>" class="org-list-item">
                        <span class="org-event-date"><?php echo e(\Illuminate\Support\Carbon::parse($event->event_date)->format('d M')); ?></span>
                        <div>
                            <strong><?php echo e($event->title); ?></strong>
                            <small class="text-muted d-block"><?php echo e($event->venue); ?></small>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted small mb-0">No upcoming events yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.organizer-calendar').forEach(calendar => {
    const events = JSON.parse(calendar.dataset.events || '{}');
    const googleBusyDates = JSON.parse(calendar.dataset.googleBusy || '{}');
    const grid = calendar.querySelector('.availability-calendar-grid');
    const monthLabel = calendar.querySelector('.organizer-calendar-month');
    const today = new Date();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();

    function dateKey(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function render() {
        monthLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        grid.innerHTML = '';

        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        for (let index = 0; index < firstDay; index++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'av-day av-day--empty';
            grid.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const key = dateKey(viewYear, viewMonth, day);
            const date = new Date(viewYear, viewMonth, day);
            const dayEvents = events[key] || [];
            const event = dayEvents[0];
            const googleBusy = googleBusyDates[key];
            const cell = document.createElement('div');

            cell.className = 'av-day';

            if (event) {
                cell.classList.add('av-day--booked', 'organizer-calendar-event');
                cell.title = dayEvents.map(item => item.title).join(', ');
            }

            if (! event && googleBusy) {
                cell.classList.add('av-day--google-busy');
                cell.title = googleBusy.summary || 'Busy on Google Calendar';
            }

            if (date < today) {
                cell.classList.add('av-day--past');
            }

            if (key === dateKey(today.getFullYear(), today.getMonth(), today.getDate())) {
                cell.classList.add('av-day--today');
            }

            const number = document.createElement('span');
            number.className = 'av-day-number';
            number.textContent = day;
            cell.appendChild(number);

            if (event) {
                const label = document.createElement('a');
                label.className = 'av-day-event';
                label.href = event.url;
                label.textContent = event.title.length > 14 ? `${event.title.slice(0, 14)}…` : event.title;
                cell.appendChild(label);

                dayEvents.slice(1).forEach(item => {
                    const more = document.createElement('a');
                    more.className = 'av-day-event';
                    more.href = item.url;
                    more.textContent = item.title.length > 14 ? `${item.title.slice(0, 14)}...` : item.title;
                    cell.appendChild(more);
                });
            } else if (googleBusy) {
                const label = document.createElement('span');
                label.className = 'av-day-google-label';
                label.textContent = 'Google';
                cell.appendChild(label);
            }

            grid.appendChild(cell);
        }
    }

    calendar.querySelectorAll('.organizer-calendar-nav').forEach(button => {
        button.addEventListener('click', () => {
            if (button.dataset.action === 'prev') {
                viewMonth -= 1;
                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear -= 1;
                }
            }

            if (button.dataset.action === 'next') {
                viewMonth += 1;
                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear += 1;
                }
            }

            if (button.dataset.action === 'today') {
                viewYear = today.getFullYear();
                viewMonth = today.getMonth();
            }

            render();
        });
    });

    render();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\calendar\index.blade.php ENDPATH**/ ?>
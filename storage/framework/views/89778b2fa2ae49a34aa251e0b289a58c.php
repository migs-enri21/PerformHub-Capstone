<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'schedules',
    'bookingCalendar' => collect(),
    'googleBusy' => [],
    'editable' => false,
    'storeUrl' => null,
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
    'schedules',
    'bookingCalendar' => collect(),
    'googleBusy' => [],
    'editable' => false,
    'storeUrl' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $scheduleMap = $schedules
        ->mapWithKeys(fn ($schedule) => [
            $schedule->date->format('Y-m-d') => [
                'id' => $schedule->id,
                'is_available' => (bool) $schedule->is_available,
                'start_time' => $schedule->start_time ? \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) : null,
                'end_time' => $schedule->end_time ? \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) : null,
                'notes' => $schedule->notes,
            ],
        ])
        ->all();

    $pendingMap = $bookingCalendar
        ->where('status', 'pending')
        ->mapWithKeys(fn ($booking) => [
            $booking->event_date->format('Y-m-d') => [
                'label' => 'Pending',
                'event_name' => $booking->event_name,
            ],
        ])
        ->all();

    $confirmedMap = $bookingCalendar
        ->whereIn('status', ['accepted', 'completed'])
        ->mapWithKeys(fn ($booking) => [
            $booking->event_date->format('Y-m-d') => [
                'event_name' => $booking->event_name,
            ],
        ])
        ->all();
?>

<div
    class="availability-calendar"
    data-editable="<?php echo e($editable ? '1' : '0'); ?>"
    data-schedules="<?php echo e(json_encode($scheduleMap)); ?>"
    data-pending="<?php echo e(json_encode($pendingMap)); ?>"
    data-confirmed="<?php echo e(json_encode($confirmedMap)); ?>"
    data-google-busy="<?php echo e(json_encode($googleBusy)); ?>"
    <?php if($editable && $storeUrl): ?>
        data-store-url="<?php echo e($storeUrl); ?>"
        data-destroy-url="<?php echo e(route('performer.availability.destroy', '__ID__')); ?>"
    <?php endif; ?>
>
    <div class="availability-calendar-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="availability-calendar-nav d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm ph-btn-outline av-cal-nav calendar-prev-btn" data-action="prev" aria-label="Previous month">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <h5 class="fw-semibold mb-0 av-cal-month-label"></h5>
            <button type="button" class="btn btn-sm ph-btn-outline av-cal-nav calendar-next-btn" data-action="next" aria-label="Next month">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
        <button type="button" class="btn btn-sm ph-btn-outline calendar-today-btn" data-action="today">Today</button>
    </div>

    <div class="availability-calendar-weekdays">
        <?php $__currentLoopData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span><?php echo e($weekday); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="availability-calendar-grid" role="grid" aria-label="Availability calendar"></div>

    <div class="availability-calendar-legend mt-3">
        <span><i class="av-legend-dot av-legend-dot--available"></i> Available</span>
        <span><i class="av-legend-dot av-legend-dot--booked"></i> Booked (event)</span>
        <span><i class="av-legend-dot av-legend-dot--pending"></i> Pending</span>
        <span><i class="av-legend-dot av-legend-dot--google"></i> Busy (Google Calendar)</span>
    </div>

    <?php if($editable): ?>
        <p class="text-muted small mt-3 mb-0">All dates are <strong>available by default</strong>. <span class="text-warning">Yellow</span> dates are pending booking requests waiting for your response.</p>
    <?php else: ?>
        <p class="text-muted small mt-3 mb-0">Green dates are open for bookings. Yellow means a pending request awaiting organizer response.</p>
    <?php endif; ?>
</div>

<?php if($editable && $storeUrl): ?>
    <div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--ph-bg-card); border-color: var(--ph-border); color: var(--ph-text);">
                <div class="modal-header" style="border-color: var(--ph-border);">
                    <h5 class="modal-title fw-semibold" id="availabilityModalLabel">Set Availability</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo e($storeUrl); ?>" id="availabilityForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <p class="text-muted small mb-3" id="availabilityModalDate"></p>
                        <input type="hidden" name="date" id="availabilityDateInput">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-muted small" for="availabilityStart">Start time</label>
                                <input type="time" name="start_time" id="availabilityStart" class="form-control ph-input">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small" for="availabilityEnd">End time</label>
                                <input type="time" name="end_time" id="availabilityEnd" class="form-control ph-input">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small d-block mb-2">Status</label>
                                <div class="d-flex flex-column gap-2">
                                    <label class="av-status-option">
                                        <input type="radio" name="availability_mode" value="available" id="availabilityModeAvailable" checked>
                                        <span><strong>Available</strong> — open for new bookings</span>
                                    </label>
                                    <label class="av-status-option">
                                        <input type="radio" name="availability_mode" value="event" id="availabilityModeEvent">
                                        <span><strong>I have an event</strong> — already booked that day</span>
                                    </label>
                                    <label class="av-status-option">
                                        <input type="radio" name="availability_mode" value="blocked" id="availabilityModeBlocked">
                                        <span><strong>Day off</strong> — not taking bookings</span>
                                    </label>
                                </div>
                                <input type="hidden" name="is_available" id="availabilityIsAvailable" value="1">
                            </div>
                            <div class="col-12" id="availabilityEventWrap">
                                <label class="form-label text-muted small" for="availabilityNotes">Event name</label>
                                <input type="text" name="notes" id="availabilityNotes" class="form-control ph-input" placeholder="e.g. Wedding gig at Marco Polo Hotel">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-color: var(--ph-border);">
                        <button type="button" class="btn ph-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn ph-btn-primary">Save</button>
                    </div>
                </form>
                <form method="POST" id="availabilityDeleteForm" class="d-none">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <div class="modal-footer pt-0" style="border-color: var(--ph-border);">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Remove this date</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('7c0a2696-11e6-436e-b144-53285aeed87d')): $__env->markAsRenderedOnce('7c0a2696-11e6-436e-b144-53285aeed87d'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
        document.querySelectorAll('.availability-calendar').forEach(calendar => {
            function parseDataset(value, fallback = {}) {
                try {
                    return JSON.parse(value || '{}');
                } catch (error) {
                    console.error('Availability calendar data parse error', error);
                    return fallback;
                }
            }

            const schedules = parseDataset(calendar.dataset.schedules);
            const pendingDates = parseDataset(calendar.dataset.pending);
            const confirmedDates = parseDataset(calendar.dataset.confirmed);
            const googleBusyDates = parseDataset(calendar.dataset.googleBusy);
            const editable = calendar.dataset.editable === '1';
            const grid = calendar.querySelector('.availability-calendar-grid');
            const monthLabel = calendar.querySelector('.av-cal-month-label');
            const destroyUrlTemplate = calendar.dataset.destroyUrl || '';

            function destroyUrlFor(id) {
                return destroyUrlTemplate.replace('__ID__', id);
            }

            const modalEl = document.getElementById('availabilityModal');
            let modal = null;

            function getModal() {
                if (!modal && modalEl && typeof bootstrap !== 'undefined') {
                    modal = new bootstrap.Modal(modalEl);
                }

                return modal;
            }
            const form = document.getElementById('availabilityForm');
            const deleteForm = document.getElementById('availabilityDeleteForm');
            const dateInput = document.getElementById('availabilityDateInput');
            const startInput = document.getElementById('availabilityStart');
            const endInput = document.getElementById('availabilityEnd');
            const notesInput = document.getElementById('availabilityNotes');
            const isAvailableInput = document.getElementById('availabilityIsAvailable');
            const eventWrap = document.getElementById('availabilityEventWrap');
            const modeInputs = form ? form.querySelectorAll('input[name="availability_mode"]') : [];
            const modalDateLabel = document.getElementById('availabilityModalDate');

            function syncAvailabilityMode() {
                if (!form) {
                    return;
                }

                const mode = form.querySelector('input[name="availability_mode"]:checked')?.value || 'available';

                if (mode === 'available') {
                    isAvailableInput.value = '1';
                    eventWrap.classList.add('d-none');
                    notesInput.removeAttribute('required');
                    notesInput.value = '';
                } else if (mode === 'event') {
                    isAvailableInput.value = '0';
                    eventWrap.classList.remove('d-none');
                    eventWrap.querySelector('label').textContent = 'Event name';
                    notesInput.placeholder = 'e.g. Wedding gig at Marco Polo Hotel';
                    notesInput.setAttribute('required', 'required');
                } else if (mode === 'blocked') {
                    isAvailableInput.value = '0';
                    eventWrap.classList.remove('d-none');
                    eventWrap.querySelector('label').textContent = 'Notes (optional)';
                    notesInput.placeholder = 'e.g. Out of town, rest day';
                    notesInput.removeAttribute('required');
                }
            }

            modeInputs.forEach(input => input.addEventListener('change', syncAvailabilityMode));

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let viewYear = today.getFullYear();
            let viewMonth = today.getMonth();

            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            function formatDateKey(year, month, day) {
                return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            }

            function formatDisplayDate(dateKey) {
                const [year, month, day] = dateKey.split('-').map(Number);
                return new Date(year, month - 1, day).toLocaleDateString(undefined, {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                });
            }

            function dayState(dateKey, entry) {
                if (confirmedDates[dateKey]) {
                    return 'booked';
                }

                if (pendingDates[dateKey]) {
                    return 'pending';
                }

                if (entry && !entry.is_available && entry.notes) {
                    return 'booked';
                }

                if (entry && !entry.is_available) {
                    return 'blocked';
                }

                if (googleBusyDates[dateKey]) {
                    return 'google-busy';
                }

                if (entry && entry.is_available) {
                    return 'available';
                }

                return 'default';
            }

            function dayTitle(dateKey, entry, state) {
                if (state === 'booked') {
                    if (entry?.notes) {
                        return `Booked: ${entry.notes}`;
                    }

                    if (confirmedDates[dateKey]) {
                        return `Booked: ${confirmedDates[dateKey].event_name}`;
                    }
                }

                if (state === 'pending') {
                    const pending = pendingDates[dateKey];
                    return `${pending.label} — ${pending.event_name} (waiting for organizer update)`;
                }

                if (state === 'blocked') {
                    return 'Day off — not taking bookings';
                }

                if (state === 'google-busy') {
                    const googleEvent = googleBusyDates[dateKey];
                    return googleEvent?.summary
                        ? `Busy (Google Calendar): ${googleEvent.summary}`
                        : 'Busy (Google Calendar)';
                }

                if (state === 'available' && entry) {
                    return 'Available';
                }

                return 'Available (default)';
            }

            function render() {
                monthLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
                grid.innerHTML = '';

                const firstDay = new Date(viewYear, viewMonth, 1).getDay();
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                for (let i = 0; i < firstDay; i++) {
                    const pad = document.createElement('div');
                    pad.className = 'av-day av-day--empty';
                    pad.setAttribute('aria-hidden', 'true');
                    grid.appendChild(pad);
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const dateKey = formatDateKey(viewYear, viewMonth, day);
                    const cellDate = new Date(viewYear, viewMonth, day);
                    const entry = schedules[dateKey];
                    const state = dayState(dateKey, entry);

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'av-day';
                    button.dataset.date = dateKey;

                    if (cellDate < today) {
                        button.classList.add('av-day--past');
                    }

                    if (dateKey === formatDateKey(today.getFullYear(), today.getMonth(), today.getDate())) {
                        button.classList.add('av-day--today');
                    }

                    if (state === 'booked') {
                        button.classList.add('av-day--booked');
                    } else if (state === 'pending') {
                        button.classList.add('av-day--pending');
                    } else if (state === 'blocked') {
                        button.classList.add('av-day--blocked');
                    } else if (state === 'google-busy') {
                        button.classList.add('av-day--google-busy');
                    } else if (state === 'available') {
                        button.classList.add('av-day--available');
                    } else if (cellDate >= today) {
                        button.classList.add('av-day--available', 'av-day--default');
                    }

                    button.title = dayTitle(dateKey, entry, state);

                    const dayNumber = document.createElement('span');
                    dayNumber.className = 'av-day-number';
                    dayNumber.textContent = day;
                    button.appendChild(dayNumber);

                    if (entry?.start_time || entry?.end_time) {
                        const time = document.createElement('span');
                        time.className = 'av-day-time';
                        time.textContent = entry.start_time && entry.end_time
                            ? `${entry.start_time}–${entry.end_time}`
                            : (entry.start_time || entry.end_time || '');
                        button.appendChild(time);
                    } else if (state === 'pending') {
                        const pending = document.createElement('span');
                        pending.className = 'av-day-pending-label';
                        pending.textContent = pendingDates[dateKey].label;
                        button.appendChild(pending);
                    } else if (state === 'google-busy') {
                        const googleLabel = document.createElement('span');
                        googleLabel.className = 'av-day-google-label';
                        googleLabel.textContent = 'Google';
                        button.appendChild(googleLabel);
                    } else if (entry?.notes && state === 'booked') {
                        const event = document.createElement('span');
                        event.className = 'av-day-event';
                        event.textContent = entry.notes.length > 14 ? `${entry.notes.slice(0, 14)}…` : entry.notes;
                        button.appendChild(event);
                    } else if (confirmedDates[dateKey]) {
                        const event = document.createElement('span');
                        event.className = 'av-day-event';
                        const name = confirmedDates[dateKey].event_name;
                        event.textContent = name.length > 14 ? `${name.slice(0, 14)}…` : name;
                        button.appendChild(event);
                    }

                    if (editable && cellDate >= today && state !== 'pending') {
                        button.addEventListener('click', () => openEditor(dateKey, entry));
                    } else if (editable && cellDate < today) {
                        button.disabled = true;
                    }

                    grid.appendChild(button);
                }
            }

            function openEditor(dateKey, entry) {
                const availabilityModal = getModal();

                if (!availabilityModal || !form) {
                    return;
                }

                modalDateLabel.textContent = formatDisplayDate(dateKey);
                dateInput.value = dateKey;
                startInput.value = entry?.start_time || '';
                endInput.value = entry?.end_time || '';
                notesInput.value = entry?.notes || '';

                let mode = 'available';
                if (entry && !entry.is_available) {
                    mode = entry.notes ? 'event' : 'blocked';
                }

                const modeInput = form.querySelector(`input[name="availability_mode"][value="${mode}"]`);
                if (modeInput) {
                    modeInput.checked = true;
                }

                syncAvailabilityMode();

                if (entry?.id && deleteForm) {
                    deleteForm.action = destroyUrlFor(entry.id);
                    deleteForm.classList.remove('d-none');
                    deleteForm.querySelector('button[type="submit"]').textContent = entry.is_available
                        ? 'Remove custom hours'
                        : 'Clear and use default (available)';
                } else if (deleteForm) {
                    deleteForm.classList.add('d-none');
                }

                availabilityModal.show();
            }

            calendar.querySelectorAll('[data-action]').forEach(button => {
                button.addEventListener('click', () => {
                    const action = button.dataset.action;

                    if (action === 'prev') {
                        viewMonth -= 1;
                        if (viewMonth < 0) {
                            viewMonth = 11;
                            viewYear -= 1;
                        }
                    } else if (action === 'next') {
                        viewMonth += 1;
                        if (viewMonth > 11) {
                            viewMonth = 0;
                            viewYear += 1;
                        }
                    } else if (action === 'today') {
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
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\availability-calendar.blade.php ENDPATH**/ ?>
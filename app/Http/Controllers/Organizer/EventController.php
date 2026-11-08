<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventPhoto;
use App\Models\EventType;
use App\Models\FeatureRequest;
use App\Models\Booking;
use App\Models\Genre;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Services\SupabaseStorageService;
use App\Support\OptionList;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;


class EventController extends Controller
{
    private const MAX_EVENTS_PER_DAY = 3;

    private const REQUIRED_GAP_HOURS = 3;

    public function index(Request $request): View
    {
        Event::refreshStatuses();

        $query = Event::with('photos')
            ->withCount(['applications', 'bookings'])
            ->where('organizer_id', Auth::id());

        $this->applyEventFilter($query, $request->status);

        $events = $query->latest()
            ->paginate(6)
            ->withQueryString();

        foreach ($events as $event) {
            if ($event->cover_photo && $event->photos->isEmpty()) {
                $event->photos()->create([
                    'file_path' => $event->cover_photo,
                    'sort_order' => 0,
                ]);
                $event->load('photos');
            }
        }

        return view('organizer.events.index', compact('events'));
    }

    public function create(): View
    {
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $genreCategories = $this->getGenreCategories();
        $eventTypeRequests = $this->featureRequestsFor(FeatureRequest::TYPE_EVENT_TYPE);
        $categoryRequests = $this->featureRequestsFor(FeatureRequest::TYPE_CATEGORY);

        return view('organizer.events.create', compact(
            'eventTypes',
            'categories',
            'genreCategories',
            'eventTypeRequests',
            'categoryRequests'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedEvent($request);

        if ($this->mediaCount($request) > 3) {
            return back()->withInput()->withErrors([
                'photos' => 'You can upload up to 3 photos or videos for one event.',
            ]);
        }

        $categoryIds = $validated['category_ids'];
        unset($validated['cover_photo'], $validated['photos'], $validated['videos'], $validated['category_ids']);

        $validated['organizer_id'] = Auth::id();
        $validated['status'] = 'Open';

        $event = Event::create($validated);

        $this->syncEventCategories($event, $categoryIds);
        $this->storeUploadedMedia($event, $request);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        Event::refreshStatuses();
        Booking::sweepPastBookings();
        $event->refresh();

        $this->authorizeEvent($event);

        $event->load(['eventType', 'categories', 'photos','applications.performer.performerProfile']);
        $bookings = Booking::where('event_id', $event->id)->get()->keyBy('performer_id');
        $canCompleteEvent = false;
        $hasConfirmedBooking = false;
        $reservedBudget = 0;
        $remainingBudget = null;
        $contestPrizePool = null;
        $hasFullAccess = ! Auth::user()->hasLimitedAccess();
        $canEditEvent = $hasFullAccess && $this->eventCanBeEdited($event);
        $applicationsClosed = strtolower($event->status) === 'open' && $event->hasStarted();
        $hasParticipants = $this->eventHasParticipants($event);
        $hasPendingCancellationRequest = $this->hasPendingCancellationRequest($event);
        $canDeleteEvent = $hasFullAccess && $this->eventCanBeDeleted($event, $hasParticipants);
        $canCancelEvent = $hasParticipants
            && strtolower($event->status) === 'open'
            && ! Auth::user()->hasLimitedAccess();

        foreach ($bookings as $booking) {
            if ($booking->status === 'completed') {
                $hasConfirmedBooking = true;
            }

            if ($booking->status === 'completed') {
                $reservedBudget += (float) $booking->budget;
            }
        }

        if ($hasFullAccess && strtolower($event->status) === 'ended' && $hasConfirmedBooking && ! $hasPendingCancellationRequest) {
            $canCompleteEvent = true;
        }

        if (in_array($event->compensation_type, ['fixed', 'hourly'], true) && $event->budget !== null) {
            $remainingBudget = (float) $event->budget - $reservedBudget;
        }

        if ($event->compensation_type === 'contest') {
            $contestPrizePool = (float) $event->first_prize
                + (float) $event->second_prize
                + (float) $event->third_prize;
        }

        return view('organizer.events.show', compact(
            'event',
            'bookings',
            'canEditEvent',
            'applicationsClosed',
            'canDeleteEvent',
            'canCompleteEvent',
            'canCancelEvent',
            'hasPendingCancellationRequest',
            'reservedBudget',
            'remainingBudget',
            'contestPrizePool'
        ));
    }

    public function edit(Event $event): View
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        if (! $this->eventCanBeEdited($event)) {
            abort(403);
        }

        $this->syncLegacyCoverPhoto($event);
        $event->load(['photos', 'categories']);
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $genreCategories = $this->getGenreCategories(
            OptionList::wrap($event->preferred_genres),
            $event->categories->pluck('id')->all()
        );
        $eventTypeRequests = $this->featureRequestsFor(FeatureRequest::TYPE_EVENT_TYPE);
        $categoryRequests = $this->featureRequestsFor(FeatureRequest::TYPE_CATEGORY);
        $hasParticipants = $this->eventHasParticipants($event);
        $confirmedBookingBudget = Booking::where('event_id', $event->id)
            ->where('status', 'completed')
            ->sum('budget');
        $canCancelEvent = $hasParticipants
            && strtolower($event->status) === 'open'
            && ! Auth::user()->hasLimitedAccess();

        return view('organizer.events.edit', compact(
            'event',
            'eventTypes',
            'categories',
            'genreCategories',
            'eventTypeRequests',
            'categoryRequests',
            'hasParticipants',
            'confirmedBookingBudget',
            'canCancelEvent'
        ));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        if (! $this->eventCanBeEdited($event)) {
            return redirect()
                ->route('organizer.events.show', $event)
                ->with('warning', 'Only open events can be edited.');
        }

        $validated = $this->validatedEvent($request, $event);

        $newMediaCount = $this->mediaCount($request);

        if ($newMediaCount > 0 && $event->photos()->count() + $newMediaCount > 3) {
            return back()->withInput()->withErrors([
                'photos' => 'An event can have up to 3 photos or videos only.',
            ]);
        }

        $categoryIds = $validated['category_ids'];
        unset($validated['cover_photo'], $validated['photos'], $validated['videos'], $validated['category_ids']);

        $event->update($validated);

        if ($event->wasChanged([
            'title',
            'description',
            'event_date',
            'end_date',
            'start_time',
            'end_time',
            'venue',
            'compensation_type',
            'budget',
            'rate_per_hour',
            'work_hours_per_day',
            'first_prize',
            'second_prize',
            'third_prize',
        ])) {
            $this->syncActiveBookingsAndNotifyPerformers($event);
        }

        $this->syncEventCategories($event, $categoryIds);
        $this->storeUploadedMedia($event, $request);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        $hasParticipants = $this->eventHasParticipants($event);
        $status = strtolower($event->status);

        if ($status === 'cancelled') {
            $this->deleteEventPhotos($event);
            $event->delete();

            return redirect()
                ->route('organizer.events.index')
                ->with('success', 'Cancelled event deleted successfully.');
        }

        if ($hasParticipants) {
            return back()->with('warning', 'This event has performer records. Cancel it instead to notify everyone and keep the history.');
        }

        if ($status !== 'ended' && ! $this->eventCanBeEdited($event)) {
            return back()->with('warning', 'Only unused open or ended events can be deleted.');
        }

        $this->deleteEventPhotos($event);
        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function destroyMedia(Event $event, EventPhoto $photo): RedirectResponse
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        if (! $this->eventCanBeEdited($event)) {
            return redirect()
                ->route('organizer.events.show', $event)
                ->with('warning', 'Media can only be changed while an event is open.');
        }

        if ($photo->event_id !== $event->id) {
            abort(404);
        }

        $wasCoverPhoto = $event->cover_photo === $photo->file_path;

        if (!str_starts_with($photo->file_path, 'http')) {
            $supabase = new SupabaseStorageService();
            $supabase->delete('organizer-files', $photo->file_path);
        }

        $photo->delete();

        if ($wasCoverPhoto) {
            $nextPhoto = $event->photos()->first();
            $newCoverPhoto = null;

            if ($nextPhoto) {
                $newCoverPhoto = $nextPhoto->file_path;
            }

            $event->update(['cover_photo' => $newCoverPhoto]);
        }

        return back()->with('success', 'Event media removed.');
    }

    public function cancel(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        if (strtolower($event->status) !== 'open') {
            return back()->with('warning', 'Only open events can be cancelled.');
        }

        if (! $this->eventHasParticipants($event)) {
            return back()->with('warning', 'This event has no performer records. Delete it instead.');
        }

        $bookings = Booking::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->with('performer')
            ->get();
        $applications = EventApplication::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'invited', 'accepted'])
            ->with('performer')
            ->get();
        $notifiedPerformerIds = [];

        foreach ($bookings as $booking) {
            $booking->update([
                'status' => 'cancelled',
                'cancel_requested_at' => null,
            ]);

            if ($booking->performer) {
                Notification::send(
                    $booking->performer,
                    'event',
                    'Event Cancelled',
                    'The organizer cancelled "'.$event->title.'". Your booking is now cancelled.',
                    route('performer.bookings.show', $booking)
                );

                $notifiedPerformerIds[] = $booking->performer_id;
            }
        }

        foreach ($applications as $application) {
            $application->update(['status' => 'cancelled']);

            if (! $application->performer || in_array($application->performer_id, $notifiedPerformerIds, true)) {
                continue;
            }

            Notification::send(
                $application->performer,
                'event',
                'Event Cancelled',
                'The organizer cancelled "'.$event->title.'".',
                route('performer.dashboard')
            );
        }

        $event->update(['status' => 'Cancelled']);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event cancelled. Affected performers were notified.');
    }

    public function complete(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);
        Event::refreshStatuses();
        $event->refresh();

        if (strtolower($event->status) === 'completed') {
            return back()->with('info', 'This event is already completed.');
        }

        if (strtolower($event->status) === 'cancelled') {
            return back()->with('warning', 'A cancelled event cannot be marked as completed.');
        }

        if (strtolower($event->status) !== 'ended') {
            return back()->with('warning', 'An event can only be marked as completed after it has ended.');
        }

        if ($this->hasPendingCancellationRequest($event)) {
            return back()->with('warning', 'Resolve pending performer cancellation requests before completing this event.');
        }

        $hasConfirmedBooking = Booking::where('event_id', $event->id)
            ->where('status', 'completed')
            ->exists();

        if (! $hasConfirmedBooking) {
            return back()->with('warning', 'Confirm at least one booking before marking this event as completed.');
        }

        $event->update(['status' => 'Completed']);

        return back()->with('success', 'Event marked as completed.');
    }

    private function authorizeEvent(Event $event): void
    {
        if ($event->organizer_id !== Auth::id()) {
            abort(403);
        }
    }

    private function eventHasParticipants(Event $event): bool
    {
        return $event->applications()->exists()
            || Booking::where('event_id', $event->id)->exists();
    }

    private function hasPendingCancellationRequest(Event $event): bool
    {
        return Booking::where('event_id', $event->id)
            ->whereNotNull('cancel_requested_at')
            ->whereIn('status', ['accepted', 'completed'])
            ->exists();
    }

    private function eventCanBeEdited(Event $event): bool
    {
        return strtolower($event->status) === 'open' && ! $event->hasStarted();
    }

    private function eventCanBeDeleted(Event $event, bool $hasParticipants): bool
    {
        $status = strtolower($event->status);

        if ($status === 'cancelled') {
            return true;
        }

        if ($hasParticipants) {
            return false;
        }

        return $status === 'ended' || $this->eventCanBeEdited($event);
    }

    private function syncActiveBookingsAndNotifyPerformers(Event $event): void
    {
        $message = 'The details for "'.$event->title.'" have been updated. Please review the event information.';
        $notifiedPerformerIds = [];
        $bookings = Booking::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->with('performer')
            ->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'event_name' => $event->title,
                'event_date' => $event->event_date,
                'end_date' => $event->end_date,
                'event_time' => Carbon::parse($event->start_time)->format('H:i'),
                'end_time' => Carbon::parse($event->end_time)->format('H:i'),
                'venue' => $event->venue,
                'requirements' => $event->description,
            ]);

            if ($booking->performer) {
                Notification::send(
                    $booking->performer,
                    'event',
                    'Event Details Updated',
                    $message,
                    route('performer.bookings.show', $booking)
                );

                $notifiedPerformerIds[] = $booking->performer_id;
            }
        }

        $applications = EventApplication::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with('performer')
            ->get();

        foreach ($applications as $application) {
            if (! $application->performer || in_array($application->performer_id, $notifiedPerformerIds, true)) {
                continue;
            }

            Notification::send(
                $application->performer,
                'event',
                'Event Details Updated',
                $message,
                route('performer.organizers.show', $event->organizer_id)
            );
        }
    }

    private function applyEventFilter($query, ?string $filter): void
    {
        if ($filter === 'open') {
            $query->whereIn('status', ['Open', 'open']);
        }

        if ($filter === 'completed') {
            $query->where('status', 'Completed');
        }

        if ($filter === 'ended') {
            $query->where('status', 'Ended');
        }

        if ($filter === 'cancelled') {
            $query->where('status', 'Cancelled');
        }
    }

    private function validatedEvent(Request $request, ?Event $event = null): array
    {
        $allowedGenres = array_keys($this->getGenreCategories());

        if ($event) {
            $eventGenres = OptionList::wrap($event->preferred_genres);
            $allowedGenres = array_merge($allowedGenres, $eventGenres);
            $allowedGenres = array_unique($allowedGenres);
            $allowedGenres = array_values($allowedGenres);
        }

        $eventDateRules = ['required', 'date', 'after_or_equal:today'];

        $rules = [
            'event_type_id' => ['required', Rule::exists('event_types', 'id')->where('is_active', true)],
            'compensation_type' => ['required', Rule::in(['fixed', 'hourly', 'contest'])],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => [Rule::exists('categories', 'id')->where('is_active', true)],
            'preferred_genres' => ['nullable', 'array'],
            'preferred_genres.*' => ['string', Rule::in($allowedGenres)],
            'title' => ['required', 'string', 'max:255'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'max:5120'],
            'videos' => ['nullable', 'array', 'max:3'],
            'videos.*' => ['file', 'mimes:mp4,webm', 'max:25600'],
            'description' => ['required', 'string', 'max:2000'],
            'event_date' => $eventDateRules,
            'end_date' => ['required', 'date', 'after_or_equal:event_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'venue' => ['required', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric'],
            'first_prize' => ['nullable', 'numeric', 'min:0'],
            'second_prize' => ['nullable', 'numeric', 'min:0'],
            'third_prize' => ['nullable', 'numeric', 'min:0'],
            'rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'work_hours_per_day' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];

        $compensationType = $request->input('compensation_type');

        if ($compensationType === 'contest') {
            $rules['first_prize'] = ['required', 'numeric', 'min:0'];
            $rules['second_prize'] = ['required', 'numeric', 'min:0'];
            $rules['third_prize'] = ['required', 'numeric', 'min:0'];
        }

        if ($compensationType === 'hourly') {
            $rules['budget'] = ['required', 'numeric', 'min:0'];
            $rules['rate_per_hour'] = ['required', 'numeric', 'min:0'];
            $rules['work_hours_per_day'] = ['required', 'integer', 'min:1', 'max:24'];
        }

        if ($compensationType === 'fixed') {
            $rules['budget'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);

        if ($validated['event_date'] === $validated['end_date'] && $validated['end_time'] <= $validated['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time for a one-day event.',
            ]);
        }

        $this->validateContestPrizeOrder($validated);

        if (!array_key_exists('preferred_genres', $validated)) {
            $validated['preferred_genres'] = [];
        }

        $this->clearUnusedCompensationFields($validated, $validated['compensation_type']);
        $this->ensureConfirmedBookingsFitEditedBudget($validated, $event);
        $this->ensureOrganizerScheduleAvailable($validated, $event);

        return $validated;
    }

    private function validateContestPrizeOrder(array $eventDetails): void
    {
        if ($eventDetails['compensation_type'] !== 'contest') {
            return;
        }

        if ($eventDetails['first_prize'] <= $eventDetails['second_prize']) {
            throw ValidationException::withMessages([
                'first_prize' => 'First prize must be higher than second prize.',
            ]);
        }

        if ($eventDetails['second_prize'] <= $eventDetails['third_prize']) {
            throw ValidationException::withMessages([
                'second_prize' => 'Second prize must be higher than third prize.',
            ]);
        }
    }

    private function ensureConfirmedBookingsFitEditedBudget(array $eventDetails, ?Event $event): void
    {
        if (! $event) {
            return;
        }

        $confirmedBookings = Booking::where('event_id', $event->id)
            ->where('status', 'completed');

        if (! $confirmedBookings->exists()) {
            return;
        }

        if ($eventDetails['compensation_type'] !== $event->compensation_type) {
            throw ValidationException::withMessages([
                'compensation_type' => 'Compensation type cannot be changed after performer bookings are confirmed.',
            ]);
        }

        if ($eventDetails['compensation_type'] === 'contest') {
            return;
        }

        $allocatedBudget = (float) $confirmedBookings->sum('budget');
        $eventBudget = (float) $eventDetails['budget'];

        if ($eventBudget < $allocatedBudget) {
            throw ValidationException::withMessages([
                'budget' => 'The total budget cannot be lower than PHP '.number_format($allocatedBudget, 2).' already allocated to confirmed bookings.',
            ]);
        }
    }

    private function ensureOrganizerScheduleAvailable(array $eventDetails, ?Event $event): void
    {
        $eventDate = $eventDetails['event_date'];
        $endDate = $eventDetails['end_date'];

        if ($eventDate < today()->toDateString()) {
            return;
        }

        $scheduledEvents = Event::where('organizer_id', Auth::id())
            ->whereDate('event_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $eventDate)
            ->whereIn('status', ['Open', 'open']);

        if ($event) {
            $scheduledEvents->where('id', '!=', $event->id);
        }

        $scheduledEvents = $scheduledEvents->get();

        $newStart = $this->eventDateTime($eventDate, $eventDetails['start_time']);
        $newEnd = $this->eventDateTime($endDate, $eventDetails['end_time']);

        if ($newEnd->lessThanOrEqualTo($newStart)) {
            throw ValidationException::withMessages([
                'end_date' => 'The event end must be after its start.',
            ]);
        }

        $this->ensureConfirmedPerformersAreAvailable($eventDate, $endDate, $event);

        $date = Carbon::parse($eventDate);
        $lastDate = Carbon::parse($endDate);

        while ($date->lessThanOrEqualTo($lastDate)) {
            $eventsOnDate = $scheduledEvents->filter(function (Event $scheduledEvent) use ($date) {
                $scheduledEndDate = $scheduledEvent->end_date;

                return $scheduledEvent->event_date->lessThanOrEqualTo($date)
                    && $scheduledEndDate->greaterThanOrEqualTo($date);
            });

            if ($eventsOnDate->count() >= self::MAX_EVENTS_PER_DAY) {
                throw ValidationException::withMessages([
                    'event_date' => 'You can schedule up to 3 active events on '.$date->format('F j, Y').'.',
                ]);
            }

            $date->addDay();
        }

        foreach ($scheduledEvents as $scheduledEvent) {
            $scheduledStart = $this->eventDateTime($scheduledEvent->event_date->toDateString(), $scheduledEvent->start_time);
            $scheduledEndTime = $scheduledEvent->end_time;

            if (! $scheduledEndTime) {
                $scheduledEndTime = $scheduledEvent->start_time;
            }

            $scheduledEndDate = $scheduledEvent->end_date;
            $scheduledEnd = $this->eventDateTime($scheduledEndDate->toDateString(), $scheduledEndTime);

            if ($scheduledEnd->lessThanOrEqualTo($scheduledStart)) {
                continue;
            }

            if (!$this->hasRequiredGap($newStart, $newEnd, $scheduledStart, $scheduledEnd)) {
                throw ValidationException::withMessages([
                    'start_time' => 'Leave at least a 3-hour gap from "'.$scheduledEvent->title.'".',
                ]);
            }
        }

        $this->ensureGoogleCalendarGap($eventDate, $endDate, $newStart, $newEnd);
    }

    private function ensureGoogleCalendarGap(string $eventDate, string $endDate, Carbon $newStart, Carbon $newEnd): void
    {
        $profile = OrganizerProfile::where('user_id', Auth::id())->first();

        if (!$profile || !$profile->google_calendar_connected) {
            return;
        }

        $busyDates = $profile->googleCalendarBusyDates()
            ->whereDate('date', '>=', $eventDate)
            ->whereDate('date', '<=', $endDate)
            ->get();

        foreach ($busyDates as $busyDate) {
            if (!$busyDate->start_time || !$busyDate->end_time) {
                throw ValidationException::withMessages([
                    'event_date' => 'Your Google Calendar has an all-day busy entry on this date.',
                ]);
            }

            $busyDateValue = $busyDate->date->toDateString();
            $busyStart = $this->eventDateTime($busyDateValue, $busyDate->start_time);
            $busyEnd = $this->eventDateTime($busyDateValue, $busyDate->end_time);

            if ($busyEnd->lessThanOrEqualTo($busyStart)) {
                $busyEnd->addDay();
            }

            if (! $this->hasRequiredGap($newStart, $newEnd, $busyStart, $busyEnd)) {
                $summary = 'a Google Calendar event';

                if ($busyDate->summary) {
                    $summary = '"'.$busyDate->summary.'" in Google Calendar';
                }

                throw ValidationException::withMessages([
                    'start_time' => 'Leave at least a 3-hour gap from '.$summary.'.',
                ]);
            }
        }
    }

    private function ensureConfirmedPerformersAreAvailable(string $eventDate, string $endDate, ?Event $event): void
    {
        if (! $event) {
            return;
        }

        $confirmedBookings = Booking::where('event_id', $event->id)
            ->where('status', 'completed')
            ->get();

        foreach ($confirmedBookings as $booking) {
            $conflict = Booking::where('performer_id', $booking->performer_id)
                ->overlappingDates($eventDate, $endDate)
                ->lockingDate()
                ->where('id', '!=', $booking->id)
                ->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'event_date' => 'This change conflicts with '.$booking->performer->name.'\'s confirmed booking for "'.$conflict->event_name.'".',
                ]);
            }
        }
    }

    private function hasRequiredGap(Carbon $firstStart, Carbon $firstEnd, Carbon $secondStart, Carbon $secondEnd): bool
    {
        $firstStartsAfterGap = $firstStart->greaterThanOrEqualTo(
            $secondEnd->copy()->addHours(self::REQUIRED_GAP_HOURS)
        );
        $secondStartsAfterGap = $secondStart->greaterThanOrEqualTo(
            $firstEnd->copy()->addHours(self::REQUIRED_GAP_HOURS)
        );

        return $firstStartsAfterGap || $secondStartsAfterGap;
    }

    private function eventDateTime(string $date, string $time): Carbon
    {
        return Carbon::parse($date.' '.$time);
    }

    private function clearUnusedCompensationFields(array &$eventDetails, string $compensationType): void
    {
        if ($compensationType === 'contest') {
            $eventDetails['budget'] = null;
            $eventDetails['rate_per_hour'] = null;
            $eventDetails['work_hours_per_day'] = null;
        }

        if ($compensationType === 'hourly') {
            $eventDetails['first_prize'] = null;
            $eventDetails['second_prize'] = null;
            $eventDetails['third_prize'] = null;
        }

        if ($compensationType === 'fixed') {
            $eventDetails['first_prize'] = null;
            $eventDetails['second_prize'] = null;
            $eventDetails['third_prize'] = null;
            $eventDetails['rate_per_hour'] = null;
            $eventDetails['work_hours_per_day'] = null;
        }
    }

    private function storeUploadedMedia(Event $event, Request $request): void
    {
        $media = $request->file('photos', []);

        foreach ($request->file('videos', []) as $video) {
            $media[] = $video;
        }

        if ($media === [] && $request->hasFile('cover_photo')) {
            $media = [$request->file('cover_photo')];
        }

        if ($media === []) {
            $this->syncLegacyCoverPhoto($event);

            return;
        }

        $supabase = new SupabaseStorageService();
        $sortOrder = ((int) $event->photos()->max('sort_order')) + 1;
        $firstPath = null;

        foreach ($media as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $folder = 'event_banner';

            if (str_starts_with($file->getMimeType(), 'video/')) {
                $folder = 'event_video';
            }

            $path = $supabase->upload($file, 'organizer-files', $folder, Auth::id());

            $event->photos()->create([
                'file_path' => $path,
                'sort_order' => $sortOrder++,
            ]);

            if ($firstPath === null) {
                $firstPath = $path;
            }
        }

        if (!$event->cover_photo && $firstPath) {
            $event->update(['cover_photo' => $firstPath]);
        }
    }

    private function mediaCount(Request $request): int
    {
        $count = count($request->file('photos', []));
        $count += count($request->file('videos', []));

        return $count;
    }

    private function syncEventCategories(Event $event, array $categoryIds): void
    {
        $event->categories()->sync($categoryIds);
    }

    private function syncLegacyCoverPhoto(Event $event): void
    {
        if ($event->cover_photo && !$event->photos()->exists()) {
            $event->photos()->create([
                'file_path' => $event->cover_photo,
                'sort_order' => 0,
            ]);
        }
    }

    private function deleteEventPhotos(Event $event): void
    {
        $supabase = new SupabaseStorageService();
        $photoPaths = $event->photos->pluck('file_path')->all();

        foreach ($event->photos as $photo) {
            if (!str_starts_with($photo->file_path, 'http')) {
                $supabase->delete('organizer-files', $photo->file_path);
            }
        }

        if (
            $event->cover_photo
            && !in_array($event->cover_photo, $photoPaths, true)
            && !str_starts_with($event->cover_photo, 'http')
        ) {
            $supabase->delete('organizer-files', $event->cover_photo);
        }
    }

    private function getGenreCategories(array $savedGenres = [], array $savedCategoryIds = []): array
    {
        $genreCategories = [];

        $genres = Genre::query()
            ->where('is_active', true)
            ->whereNotNull('category_id')
            ->orderBy('name')
            ->get(['name', 'category_id']);

        foreach ($genres as $genre) {
            $genreCategories[$genre->name] = [(string) $genre->category_id];
        }

        foreach ($savedGenres as $genre) {
            if (!isset($genreCategories[$genre]) && $savedCategoryIds !== []) {
                $genreCategories[$genre] = array_map('strval', $savedCategoryIds);
            }
        }

        ksort($genreCategories);

        return $genreCategories;
    }

    private function featureRequestsFor(string $type)
    {
        return FeatureRequest::where('requester_id', Auth::id())
            ->where('type', $type)
            ->latest()
            ->get();
    }
}

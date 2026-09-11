<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Booking;
use App\Services\SupabaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class EventController extends Controller
{
    public function index(Request $request): View
    {
        Event::markPastEventsEnded();

        $query = Event::with('photos')->where('organizer_id', Auth::id());

        $this->applyEventFilter($query, $request->status);

        $events = $query->latest()->get();

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
        $categories = Category::orderBy('name')->get();
        $genreCategories = $this->getGenreCategories();

        return view('organizer.events.create', compact('eventTypes', 'categories', 'genreCategories'));
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
        Event::markPastEventsEnded();
        $event->refresh();

        $this->authorizeEvent($event);

        $event->load(['eventType', 'categories', 'photos','applications.performer.performerProfile']);
        $bookings = Booking::where('event_id', $event->id)->get()->keyBy('performer_id');
        $canCompleteEvent = false;
        $hasConfirmedBooking = false;
        $reservedBudget = 0;
        $remainingBudget = null;

        foreach ($bookings as $booking) {
            if ($booking->status === 'completed') {
                $hasConfirmedBooking = true;
            }

            if ($booking->status === 'completed') {
                $reservedBudget += (float) $booking->budget;
            }
        }

        if (in_array(strtolower($event->status), ['open', 'ended']) && $hasConfirmedBooking) {
            $canCompleteEvent = true;
        }

        if ($event->compensation_type === 'fixed' && $event->budget !== null) {
            $remainingBudget = (float) $event->budget - $reservedBudget;
        }

        return view('organizer.events.show', compact(
            'event',
            'bookings',
            'canCompleteEvent',
            'reservedBudget',
            'remainingBudget'
        ));
    }

    public function edit(Event $event): View
    {
        $this->authorizeEvent($event);

        $event->load(['photos', 'categories']);
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $genreCategories = $this->getGenreCategories();

        return view('organizer.events.edit', compact('event', 'eventTypes', 'categories', 'genreCategories'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($event->status !== 'Completed' && $request->input('status') === 'Completed') {
            return back()->withInput()->withErrors([
                'status' => 'Use the Mark Event Completed button after confirming a booking.',
            ]);
        }

        $validated = $this->validatedEvent($request, true);

        $newMediaCount = $this->mediaCount($request);

        if ($newMediaCount > 0 && $event->photos()->count() + $newMediaCount > 3) {
            return back()->withInput()->withErrors([
                'photos' => 'An event can have up to 3 photos or videos only.',
            ]);
        }

        $categoryIds = $validated['category_ids'];
        unset($validated['cover_photo'], $validated['photos'], $validated['videos'], $validated['category_ids']);

        $event->update($validated);

        $this->syncEventCategories($event, $categoryIds);
        $this->storeUploadedMedia($event, $request);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $this->deleteEventPhotos($event);
        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function complete(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        if (strtolower($event->status) === 'completed') {
            return back()->with('info', 'This event is already completed.');
        }

        if (strtolower($event->status) === 'cancelled') {
            return back()->with('warning', 'A cancelled event cannot be marked as completed.');
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

    private function applyEventFilter($query, ?string $filter): void
    {
        $today = now()->toDateString();

        if ($filter === 'upcoming') {
            $query->where('status', 'Open')->whereDate('event_date', '>', $today);
        }

        if ($filter === 'ongoing') {
            $query->where('status', 'Open')->whereDate('event_date', $today);
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

    private function validatedEvent(Request $request, bool $updating = false): array
    {
        $rules = [
            'event_type_id' => ['required', 'exists:event_types,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
            'preferred_genres' => ['nullable', 'array'],
            'preferred_genres.*' => ['string', Rule::in(array_keys($this->getGenreCategories()))],
            'title' => ['required', 'string', 'max:255'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'max:5120'],
            'videos' => ['nullable', 'array', 'max:3'],
            'videos.*' => ['file', 'mimes:mp4,webm', 'max:25600'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'venue' => ['required', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric'],
            'first_prize' => ['nullable', 'numeric', 'min:0'],
            'second_prize' => ['nullable', 'numeric', 'min:0'],
            'third_prize' => ['nullable', 'numeric', 'min:0'],
            'rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'status' => $this->statusRules($updating),
        ];

        $eventType = EventType::find($request->input('event_type_id'));

        if ($eventType && $eventType->compensation_type === 'contest') {
            $rules['first_prize'] = ['required', 'numeric', 'min:0'];
            $rules['second_prize'] = ['required', 'numeric', 'min:0'];
            $rules['third_prize'] = ['required', 'numeric', 'min:0'];
        }

        if ($eventType && $eventType->compensation_type === 'hourly') {
            $rules['rate_per_hour'] = ['required', 'numeric', 'min:0'];
        }

        if ($eventType && $eventType->compensation_type === 'fixed') {
            $rules['budget'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);

        if (! array_key_exists('preferred_genres', $validated)) {
            $validated['preferred_genres'] = [];
        }

        if ($eventType) {
            $validated['compensation_type'] = $eventType->compensation_type;
            $this->clearUnusedCompensationFields($validated, $eventType->compensation_type);
        }

        return $validated;
    }

    private function clearUnusedCompensationFields(array &$eventDetails, string $compensationType): void
    {
        if ($compensationType === 'contest') {
            $eventDetails['budget'] = null;
            $eventDetails['rate_per_hour'] = null;
        }

        if ($compensationType === 'hourly') {
            $eventDetails['budget'] = null;
            $eventDetails['first_prize'] = null;
            $eventDetails['second_prize'] = null;
            $eventDetails['third_prize'] = null;
        }

        if ($compensationType === 'fixed') {
            $eventDetails['first_prize'] = null;
            $eventDetails['second_prize'] = null;
            $eventDetails['third_prize'] = null;
            $eventDetails['rate_per_hour'] = null;
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
            if (! $file->isValid()) {
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

        if (! $event->cover_photo && $firstPath) {
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

    private function statusRules(bool $updating): array
    {
        if ($updating) {
            return ['required', 'in:Open,Ended,Completed,Cancelled'];
        }

        return ['nullable'];
    }

    private function syncLegacyCoverPhoto(Event $event): void
    {
        if ($event->cover_photo && ! $event->photos()->exists()) {
            $event->photos()->create([
                'file_path' => $event->cover_photo,
                'sort_order' => 0,
            ]);
        }
    }

    private function deleteEventPhotos(Event $event): void
    {
        $supabase = new SupabaseStorageService();

        foreach ($event->photos as $photo) {
            if (! str_starts_with($photo->file_path, 'http')) {
                $supabase->delete('organizer-files', $photo->file_path);
            }
        }

        if ($event->cover_photo && ! str_starts_with($event->cover_photo, 'http')) {
            $supabase->delete('organizer-files', $event->cover_photo);
        }
    }

    private function getGenreCategories(): array
    {
        $genreCategories = [];

        foreach (config('organizer_genre_styles', []) as $categoryName => $genres) {
            foreach ($genres as $genre) {
                if (! isset($genreCategories[$genre])) {
                    $genreCategories[$genre] = [];
                }

                $genreCategories[$genre][] = $categoryName;
            }
        }

        ksort($genreCategories);

        return $genreCategories;
    }
}

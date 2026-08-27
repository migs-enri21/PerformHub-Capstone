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
use Illuminate\View\View;


class EventController extends Controller
{
    public function index(Request $request): View
    {
        Event::completePastEvents();

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

        return view('organizer.events.create', compact('eventTypes', 'categories'));
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
        Event::completePastEvents();
        $event->refresh();

        $this->authorizeEvent($event);

        $event->load(['eventType', 'categories', 'photos','applications.performer.performerProfile']);
        $bookings = Booking::where('event_id', $event->id)->get()->keyBy('performer_id');
        $canCompleteEvent = false;
        $hasConfirmedBooking = false;

        foreach ($bookings as $booking) {
            if ($booking->status === 'completed') {
                $hasConfirmedBooking = true;
                break;
            }
        }

        if (strtolower($event->status) === 'open' && $hasConfirmedBooking) {
            $canCompleteEvent = true;
        }

        return view('organizer.events.show', compact('event', 'bookings', 'canCompleteEvent'));
    }

    public function edit(Event $event): View
    {
        $this->authorizeEvent($event);

        $event->load(['photos', 'categories']);
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('organizer.events.edit', compact('event', 'eventTypes', 'categories'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

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
            $query->where(function ($events) use ($today) {
                $events->where('status', 'Completed')
                    ->orWhere(function ($events) use ($today) {
                        $events->whereDate('event_date', '<', $today)
                            ->where('status', '!=', 'Cancelled');
                    });
            });
        }

        if ($filter === 'cancelled') {
            $query->where('status', 'Cancelled');
        }
    }

    private function validatedEvent(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'event_type_id' => ['required', 'exists:event_types,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
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
            'status' => $this->statusRules($updating),

        ]);
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
            return ['required', 'in:Open,Cancelled'];
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
}

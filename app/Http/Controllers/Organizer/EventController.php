<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventType;
use App\Services\SupabaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with('photos')->where('organizer_id', Auth::id());

        if ($request->filled('status')) {
            match ($request->status) {
                'upcoming' => $query->whereIn('status', ['Open', 'open', 'upcoming'])
                    ->whereDate('event_date', '>=', now()->toDateString()),
                'ongoing' => $query->whereDate('event_date', now()->toDateString())
                    ->whereNotIn('status', ['cancelled', 'Cancelled', 'completed', 'Completed']),
                'completed' => $query->whereIn('status', ['completed', 'Completed']),
                'cancelled' => $query->whereIn('status', ['cancelled', 'Cancelled']),
                default => $query->where('status', $request->status),
            };
        }

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
        unset($validated['cover_photo'], $validated['photos']);

        $event = Event::create([
            ...$validated,
            'organizer_id' => Auth::id(),
            'preferred_category_id' => $validated['preferred_category_id'] ?? null,
            'status' => 'Open',
        ]);

        $this->storeUploadedPhotos($event, $request);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $this->authorizeEvent($event);

        $event->load(['eventType', 'preferredCategory', 'photos']);

        return view('organizer.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorizeEvent($event);

        $event->load('photos');
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('organizer.events.edit', compact('event', 'eventTypes', 'categories'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $this->validatedEvent($request, true);
        unset($validated['cover_photo'], $validated['photos']);

        $event->update([
            ...$validated,
            'preferred_category_id' => $validated['preferred_category_id'] ?? null,
        ]);

        $this->storeUploadedPhotos($event, $request);

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

    private function authorizeEvent(Event $event): void
    {
        if ($event->organizer_id !== Auth::id()) {
            abort(403);
        }
    }

    private function validatedEvent(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'event_type_id' => ['required', 'exists:event_types,id'],
            'preferred_category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'venue' => ['required', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric'],
            'performers_needed' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function storeUploadedPhotos(Event $event, Request $request): void
    {
        $files = [];

        if ($request->hasFile('photos')) {
            $files = array_values(array_filter($request->file('photos'), fn ($file) => $file instanceof UploadedFile && $file->isValid()));
        }

        if ($files === [] && $request->hasFile('cover_photo')) {
            $files = [$request->file('cover_photo')];
        }

        if ($files === []) {
            $this->syncLegacyCoverPhoto($event);

            return;
        }

        $supabase = new SupabaseStorageService();
        $sortOrder = ((int) $event->photos()->max('sort_order')) + 1;
        $firstPath = null;

        foreach ($files as $file) {
            $path = $supabase->upload($file, 'organizer-files', 'event_banner', Auth::id());

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

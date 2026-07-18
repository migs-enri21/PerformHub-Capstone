<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Category;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
    $query = Event::where('organizer_id', Auth::id());

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $events = $query->latest()->get();

    return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();

        $categories = Category::orderBy('name')->get();

        return view('organizer.events.create', compact('eventTypes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'event_type_id' => ['required', 'exists:event_types,id'],
        'preferred_category_id' => ['nullable', 'exists:categories,id'],
        'title' => ['required', 'string', 'max:255'],
        'cover_photo' => 'nullable|image|max:5120',
        'description' => ['nullable', 'string'],
        'event_date' => ['required', 'date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'venue' => ['required', 'string', 'max:255'],
        'budget' => ['nullable', 'numeric'],
        'performers_needed' => ['required', 'integer', 'min:1'],
        ]);

        $bannerPath = null;

        if ($request->hasFile('cover_photo')) {

            $supabase = new SupabaseStorageService();

            $bannerPath = $supabase->upload($request->file('cover_photo'),'organizer-files','event_banner',Auth::id());

        }

        Event::create([
        'organizer_id' => Auth::id(),
        'event_type_id' => $validated['event_type_id'],
        'preferred_category_id' => $validated['preferred_category_id'] ?? null,
        'title' => $validated['title'],
        'description' => $validated['description'],
        'event_date' => $validated['event_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'venue' => $validated['venue'],
        'budget' => $validated['budget'],
        'performers_needed' => $validated['performers_needed'],
        'cover_photo' => $bannerPath,
        'status' => 'Open',
        ]);

        return redirect()
        ->route('organizer.events.index')
        ->with('success', 'Event created successfully.');
    }

    public function show(Event $event)
    {

    }

    public function edit(Event $event)
    {

    }

    public function update(Request $request, Event $event)
    {

    }

    public function destroy(Event $event)
    {

    }
}
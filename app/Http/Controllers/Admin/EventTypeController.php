<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventTypeController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $status = request('status');

        $query = EventType::query();

        if ($search) {
            $query->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
        }

        if ($status !== null) {
            $query->where('is_active', $status === 'active');
        }

        $eventTypes = $query->latest()->paginate(15);

        return view('admin.event-types.index', compact('eventTypes', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:event_types,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($validated['name']);
        $counter = 1;
        $baseSlug = $slug;

        while (EventType::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        EventType::create([
            ...$validated,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return back()->with('success', 'Event type created.');
    }

    public function edit(EventType $eventType): View
    {
        return view('admin.event-types.edit', compact('eventType'));
    }

    public function show(EventType $eventType): View
    {
        return view('admin.event-types.show', compact('eventType'));
    }

    public function update(Request $request, EventType $eventType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('event_types', 'name')->ignore($eventType->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $counter = 1;
        $baseSlug = $slug;

        while (EventType::where('slug', $slug)
            ->where('id', '!=', $eventType->id)
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $eventType->update([
            ...$validated,
            'slug' => $slug,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Event type updated.');
    }

    public function destroy(EventType $eventType): RedirectResponse
    {
        $eventType->delete();

        return back()->with('success', 'Event type deleted.');
    }

    public function toggle(EventType $eventType): RedirectResponse
    {
        $eventType->update(['is_active' => !$eventType->is_active]);

        $status = $eventType->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Event type $status.");
    }
}

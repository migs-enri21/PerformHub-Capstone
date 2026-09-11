<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Specialty;
use App\Support\StoredOptionNames;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $status = request('status');

        $genres = $this->filtered(Genre::query(), $search, $status)
            ->orderBy('name')
            ->paginate(15);

        $specialties = $this->filtered(Specialty::query(), $search, $status)
            ->orderBy('name')
            ->paginate(15, ['*'], 'specialties_page');

        return view('admin.genres.index', compact('genres', 'specialties', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:genres,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Genre::create([
            ...$validated,
            'slug' => Genre::makeSlug($validated['name']),
            'is_active' => true,
        ]);

        return back()->with('success', 'Genre created.');
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function show(Genre $genre): View
    {
        return view('admin.genres.show', compact('genre'));
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('genres', 'name')->ignore($genre->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $oldName = $genre->name;

        $genre->update([
            ...$validated,
            'slug' => Genre::makeSlug($validated['name'], $genre->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        StoredOptionNames::renameOnPerformers('genre', $oldName, $genre->name);
        StoredOptionNames::renameOnEvents($oldName, $genre->name);

        return back()->with('success', 'Genre updated.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $genre->delete();

        return redirect()->route('admin.genres.index')->with('success', 'Genre deleted.');
    }

    public function toggle(Genre $genre): RedirectResponse
    {
        $genre->update(['is_active' => ! $genre->is_active]);

        $status = $genre->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Genre $status.");
    }

    private function filtered($query, ?string $search, ?string $status)
    {
        if ($search) {
            $query->where(function ($match) use ($search) {
                $match->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status === 'active' || $status === 'inactive') {
            $query->where('is_active', $status === 'active');
        }

        return $query;
    }
}

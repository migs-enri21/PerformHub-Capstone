<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Support\StoredOptionNames;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.genres.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:specialties,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Specialty::create([
            ...$validated,
            'slug' => Specialty::makeSlug($validated['name']),
            'is_active' => true,
        ]);

        return back()->with('success', 'Specialty created.');
    }

    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function show(Specialty $specialty): View
    {
        return view('admin.specialties.show', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('specialties', 'name')->ignore($specialty->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $oldName = $specialty->name;

        $specialty->update([
            ...$validated,
            'slug' => Specialty::makeSlug($validated['name'], $specialty->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        StoredOptionNames::renameOnPerformers('specialty', $oldName, $specialty->name);

        return back()->with('success', 'Specialty updated.');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        $specialty->delete();

        return redirect()->route('admin.genres.index')->with('success', 'Specialty deleted.');
    }

    public function toggle(Specialty $specialty): RedirectResponse
    {
        $specialty->update(['is_active' => ! $specialty->is_active]);

        $status = $specialty->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Specialty $status.");
    }
}

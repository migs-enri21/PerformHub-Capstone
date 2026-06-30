<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $profile = Auth::user()->organizerProfile;

        return view('organizer.profile.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = Auth::user()->organizerProfile;

        $validated = $request->validate(array_merge([
            'organization_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ], PhilippineLocations::locationFieldsRules(required: false)));

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                Storage::disk('public')->delete($profile->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $profile->update(collect($validated)->except(['region', 'city', 'barangay'])->all());

        if (! empty($validated['region']) && ! empty($validated['city']) && ! empty($validated['barangay'])) {
            $profile->update(PhilippineLocations::profileLocationAttributes($validated));
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}

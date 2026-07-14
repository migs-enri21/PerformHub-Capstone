<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Services\SupabaseStorageService;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'organization_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'banner_photo' => ['nullable', 'image', 'max:5120'],
            'banner_position_y' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], PhilippineLocations::locationFieldsRules(required: false)));

        if ($request->hasFile('profile_photo')) {
            $supabase = new SupabaseStorageService();

            if ($profile->profile_photo) {
                $supabase->delete('organizer-files', $profile->profile_photo);
            }
            $validated['profile_photo'] = $supabase->upload($request->file('profile_photo'), 'organizer-files', 'profile_picture', Auth::id());
        }

        if ($request->hasFile('banner_photo')) {
            $supabase = new SupabaseStorageService();

            if ($profile->banner_photo) {
                $supabase->delete('organizer-files', $profile->banner_photo);
            }
            $validated['banner_photo'] = $supabase->upload($request->file('banner_photo'), 'organizer-files', 'banner_photo', Auth::id());
        }

        Auth::user()->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);

        $profile->update(collect($validated)->except(['first_name', 'last_name', 'region', 'city', 'barangay'])->all());

        if (! empty($validated['region']) && ! empty($validated['city']) && ! empty($validated['barangay'])) {
            $profile->update(PhilippineLocations::profileLocationAttributes($validated));
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}

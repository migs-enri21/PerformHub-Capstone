<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Services\SupabaseStorageService;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $profile = $this->getProfile();

        return view('organizer.profile.show', compact('profile'));
    }

    public function edit(): View
    {
        $profile = $this->getProfile();

        return view('organizer.profile.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->getProfile();
        $validated = $this->validateProfile($request);

        $validated = $this->storePhoto($request, $profile, $validated, 'profile_photo', 'profile_picture');
        $validated = $this->storePhoto($request, $profile, $validated, 'banner_photo', 'banner_photo');

        $this->updateUserName($validated);
        $this->updateProfileDetails($profile, $validated);

        return redirect()
            ->route('organizer.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    private function getProfile(): OrganizerProfile
    {
        return Auth::user()->organizerProfile()->firstOrFail();
    }

    private function validateProfile(Request $request): array
    {
        return $request->validate(array_merge([
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
    }

    private function storePhoto(Request $request, OrganizerProfile $profile, array $data, string $field, string $type): array
    {
        if (! $request->hasFile($field)) {
            return $data;
        }

        $storage = new SupabaseStorageService();

        if ($profile->$field) {
            $storage->delete('organizer-files', $profile->$field);
        }

        $data[$field] = $storage->upload(
            $request->file($field),
            'organizer-files',
            $type,
            Auth::id()
        );

        return $data;
    }

    private function updateUserName(array $data): void
    {
        Auth::user()->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);
    }

    private function updateProfileDetails(OrganizerProfile $profile, array $data): void
    {
        $profile->update(collect($data)->except([
            'first_name',
            'last_name',
            'region',
            'city',
            'barangay',
        ])->all());

        if (! empty($data['region']) && ! empty($data['city']) && ! empty($data['barangay'])) {
            $profile->update(PhilippineLocations::profileLocationAttributes($data));
        }
    }
}

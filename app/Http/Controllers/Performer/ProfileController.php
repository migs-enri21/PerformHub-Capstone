<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\GoogleCalendarService;
use App\Services\SupabaseStorageService;
use App\Support\AvailabilityCalendar;
use App\Support\PerformerGenres;
use App\Support\PhilippineLocations;
use App\Support\SocialMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(GoogleCalendarService $googleCalendar): View
    {
        $profile = Auth::user()->performerProfile()->with('categories')->firstOrFail();
        $profile = AvailabilityCalendar::loadCalendarRelations($profile);

        if ($googleCalendar->shouldSync($profile)) {
            try {
                $googleCalendar->syncBusyDates($profile);
                $profile = AvailabilityCalendar::loadCalendarRelations($profile->fresh('categories'));
            } catch (\Throwable) {
                // Keep the page usable even if Google sync fails.
            }
        }

        $calendar = AvailabilityCalendar::calendarData($profile);

        return view('performer.profile.show', compact('profile', 'calendar'));
    }

    public function edit(): View
    {
        $profile = Auth::user()->performerProfile()->with('categories')->firstOrFail();
        $categories = Category::where('is_active', true)->get();

        return view('performer.profile.edit', compact('profile', 'categories'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile()->firstOrFail();

        $validated = $request->validate(array_merge([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'stage_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'genre' => PerformerGenres::validationRule(),
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_facebook_followers' => ['nullable', 'integer', 'min:0'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_instagram_followers' => ['nullable', 'integer', 'min:0'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_youtube_subscribers' => ['nullable', 'integer', 'min:0'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'social_tiktok_followers' => ['nullable', 'integer', 'min:0'],
            'social_twitter' => ['nullable', 'url', 'max:255'],
            'social_twitter_followers' => ['nullable', 'integer', 'min:0'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'banner_photo' => ['nullable', 'image', 'max:5120'],
            'banner_position_y' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], PhilippineLocations::locationFieldsRules(required: false)));

        $supabase = new SupabaseStorageService();

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                $supabase->delete('performer-files', $profile->profile_photo);
            }
            $validated['profile_photo'] = $supabase->upload($request->file('profile_photo'), 'performer-files', 'profile_picture', Auth::id());
        }

        if ($request->hasFile('banner_photo')) {
            if ($profile->banner_photo) {
                $supabase->delete('performer-files', $profile->banner_photo);
            }
            $validated['banner_photo'] = $supabase->upload($request->file('banner_photo'), 'performer-files', 'banner_photo', Auth::id());
        }

        if (! empty($validated['social_youtube']) && empty($validated['social_youtube_subscribers'])) {
            $subscriberCount = SocialMedia::fetchYoutubeSubscriberCount($validated['social_youtube']);

            if ($subscriberCount !== null) {
                $validated['social_youtube_subscribers'] = $subscriberCount;
            }
        }

        Auth::user()->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);

        $profile->update(collect($validated)->except(['first_name', 'last_name', 'region', 'city', 'barangay', 'category_ids'])->all());

        $profile->categories()->sync($validated['category_ids'] ?? []);

        if (! empty($validated['region']) && ! empty($validated['city']) && ! empty($validated['barangay'])) {
            $profile->update(PhilippineLocations::profileLocationAttributes($validated));
        }

        return redirect()->route('performer.profile.show')->with('success', 'Profile updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\FeatureRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeatureRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:category,event_type'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $alreadyRequested = FeatureRequest::query()
            ->where('type', $validated['type'])
            ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->where('status', FeatureRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyRequested) {
            return back()->with('warning', 'A request for this option is already waiting for admin review.');
        }

        $validated['requester_id'] = Auth::id();

        $featureRequest = FeatureRequest::create($validated);

        $requester = Auth::user();
        $typeLabel = $featureRequest->typeLabel();

        foreach (User::where('role', User::ROLE_ADMIN)->get() as $admin) {
            Notification::send(
                $admin,
                'feature.requested',
                "New {$typeLabel} Request",
                "{$requester->fullName()} requested a new {$typeLabel}: {$featureRequest->name}.",
                route('admin.feature-requests.index')
            );
        }

        return back()->with('success', "{$typeLabel} request sent to the admin for review.");
    }

    public function destroy(FeatureRequest $featureRequest): RedirectResponse
    {
        if ($featureRequest->requester_id !== Auth::id()) {
            abort(403);
        }

        if ($featureRequest->status !== FeatureRequest::STATUS_PENDING) {
            return back()->with('warning', 'Only pending requests can be retracted.');
        }

        $typeLabel = $featureRequest->typeLabel();
        $featureRequest->delete();

        return back()->with('success', "{$typeLabel} request retracted.");
    }

    public function clearReviewed(string $type): RedirectResponse
    {
        if ($type !== FeatureRequest::TYPE_CATEGORY && $type !== FeatureRequest::TYPE_EVENT_TYPE) {
            abort(404);
        }

        $cleared = FeatureRequest::where('requester_id', Auth::id())
            ->where('type', $type)
            ->whereIn('status', [FeatureRequest::STATUS_APPROVED, FeatureRequest::STATUS_REJECTED])
            ->delete();

        if ($cleared === 0) {
            return back()->with('info', 'There are no reviewed requests to clear.');
        }

        return back()->with('success', 'Reviewed requests cleared.');
    }
}

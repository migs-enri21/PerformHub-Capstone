<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\FeatureRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FeatureRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in([FeatureRequest::TYPE_GENRE, FeatureRequest::TYPE_SPECIALTY])],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required_if:type,genre', 'nullable', 'integer', 'exists:categories,id'],
        ]);

        if ($validated['type'] === FeatureRequest::TYPE_SPECIALTY) {
            $validated['category_id'] = null;
        }

        $alreadyRequested = FeatureRequest::query()
            ->where('type', $validated['type'])
            ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->where('status', FeatureRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyRequested) {
            return back()->with('warning', 'A request for this option is already waiting for admin review.');
        }

        $featureRequest = FeatureRequest::create([
            ...$validated,
            'requester_id' => Auth::id(),
        ]);

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
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EventType;
use App\Models\FeatureRequest;
use App\Models\Genre;
use App\Models\Notification;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeatureRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $status = $request->query('status');
        $types = [
            FeatureRequest::TYPE_CATEGORY,
            FeatureRequest::TYPE_EVENT_TYPE,
            FeatureRequest::TYPE_GENRE,
            FeatureRequest::TYPE_SPECIALTY,
        ];
        $statuses = [
            FeatureRequest::STATUS_PENDING,
            FeatureRequest::STATUS_APPROVED,
            FeatureRequest::STATUS_REJECTED,
        ];

        if (! in_array($type, $types, true)) {
            $type = '';
        }

        if (! in_array($status, $statuses, true)) {
            $status = '';
        }

        $requestsQuery = FeatureRequest::with(['requester', 'category'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('requester', function (Builder $requesterQuery) use ($search): void {
                            $requesterQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($type !== '', fn (Builder $query) => $query->where('type', $type))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status));

        $requests = $requestsQuery->latest()->paginate(20)->withQueryString();

        return view('admin.feature-requests.index', compact('requests', 'search', 'type', 'status'));
    }

    public function approve(FeatureRequest $featureRequest): RedirectResponse
    {
        if ($featureRequest->status !== FeatureRequest::STATUS_PENDING) {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $optionName = $featureRequest->name;

        DB::transaction(function () use ($featureRequest, $optionName): void {
            if ($featureRequest->type === FeatureRequest::TYPE_CATEGORY) {
                abort_if(Category::whereRaw('LOWER(name) = ?', [strtolower($optionName)])->exists(), 422, 'This category already exists.');

                Category::create([
                    'name' => $optionName,
                    'slug' => $this->uniqueSlug(Category::class, $optionName),
                    'description' => $featureRequest->description,
                    'is_active' => true,
                ]);
            } elseif ($featureRequest->type === FeatureRequest::TYPE_GENRE) {
                abort_if(Genre::whereRaw('LOWER(name) = ?', [strtolower($optionName)])->exists(), 422, 'This genre already exists.');
                abort_if(! $featureRequest->category_id, 422, 'This genre request has no category.');

                Genre::create([
                    'name' => $optionName,
                    'category_id' => $featureRequest->category_id,
                    'slug' => Genre::makeSlug($optionName),
                    'description' => $featureRequest->description,
                    'is_active' => true,
                ]);
            } elseif ($featureRequest->type === FeatureRequest::TYPE_SPECIALTY) {
                abort_if(Specialty::whereRaw('LOWER(name) = ?', [strtolower($optionName)])->exists(), 422, 'This specialty already exists.');

                Specialty::create([
                    'name' => $optionName,
                    'slug' => Specialty::makeSlug($optionName),
                    'description' => $featureRequest->description,
                    'is_active' => true,
                ]);
            } else {
                abort_if(EventType::whereRaw('LOWER(name) = ?', [strtolower($optionName)])->exists(), 422, 'This event type already exists.');

                EventType::create([
                    'name' => $optionName,
                    'slug' => $this->uniqueSlug(EventType::class, $optionName),
                    'description' => $featureRequest->description,
                    'is_active' => true,
                ]);
            }

            $featureRequest->update([
                'status' => FeatureRequest::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        Notification::send(
            $featureRequest->requester,
            'feature.approved',
            "{$featureRequest->typeLabel()} Request Approved",
            "Your requested {$featureRequest->typeLabel()} '{$optionName}' is now available.",
            $this->reviewerNotificationUrl($featureRequest)
        );

        return back()->with('success', "{$featureRequest->typeLabel()} created and the requester was notified.");
    }

    public function reject(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        if ($featureRequest->status !== FeatureRequest::STATUS_PENDING) {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $featureRequest->update([
            'status' => FeatureRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        Notification::send(
            $featureRequest->requester,
            'feature.rejected',
            "{$featureRequest->typeLabel()} Request Declined",
            "Your requested {$featureRequest->typeLabel()} '{$featureRequest->name}' was not approved. Reason: {$validated['reason']}",
            $this->reviewerNotificationUrl($featureRequest)
        );

        return back()->with('success', 'Request declined and the requester was notified.');
    }

    private function reviewerNotificationUrl(FeatureRequest $featureRequest): string
    {
        return in_array($featureRequest->type, [FeatureRequest::TYPE_GENRE, FeatureRequest::TYPE_SPECIALTY], true)
            ? route('performer.profile.edit')
            : route('organizer.events.create');
    }

    private function uniqueSlug(string $model, string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'option';
        $slug = $baseSlug;
        $counter = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

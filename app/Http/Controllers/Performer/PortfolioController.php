<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Portfolio;
use App\Services\SupabaseStorageService;
use App\Support\PortfolioFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $profile = Auth::user()->performerProfile()->with('categories')->firstOrFail();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $portfolios = $profile->portfolios()->latest()->get();

        $portfolioGroups = PortfolioFeed::groupItems($portfolios);

        return view('performer.portfolio.index', compact('portfolioGroups', 'profile', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile;

        $validated = $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:512000', // 500 MB per file (kilobytes) — Supabase project's storage size ceiling
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/x-msvideo',
            ],
            'event_name' => ['nullable', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ], [
            'category_ids.required' => 'Choose whether this sample shows you singing, dancing, or another role.',
            'category_ids.min' => 'Choose whether this sample shows you singing, dancing, or another role.',
            'files.*.max' => 'Each photo or video must be 500 MB or smaller.',
            'files.*.mimetypes' => 'One of your files is not a supported photo or video format.',
        ]);

        $eventName = $validated['event_name'] ?? null;
        $caption = $validated['caption'] ?? null;
        $categoryIds = array_values(array_unique(array_map('intval', $validated['category_ids'])));
        $uploaded = 0;
        $supabase = new SupabaseStorageService();
        $batchKey = Str::uuid()->toString();

        foreach ($this->uploadedFiles($request) as $file) {
            [$type, $supabaseType] = $this->fileTypes($file);
            $path = $supabase->upload($file, 'performer-files', $supabaseType, Auth::id());

            $profile->portfolios()->create([
                'batch_key' => $batchKey,
                'type' => $type,
                'file_path' => $path,
                'event_name' => $eventName,
                'caption' => $caption,
                'category_ids' => $categoryIds,
            ]);

            $uploaded++;
        }

        if ($uploaded === 1) {
            $message = 'Portfolio item uploaded.';
        } else {
            $message = "{$uploaded} portfolio items uploaded.";
        }

        return redirect()
            ->route('performer.portfolio.index')
            ->with('success', $message);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile;

        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
            'remove_ids' => ['nullable', 'array'],
            'remove_ids.*' => ['integer'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'event_name' => ['nullable', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array'],
            'files.*' => [
                'nullable',
                'file',
                'max:512000', // 500 MB per file (kilobytes) — Supabase project's storage size ceiling
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/x-msvideo',
            ],
        ], [
            'category_ids.required' => 'Choose whether this sample shows you singing, dancing, or another role.',
            'category_ids.min' => 'Choose whether this sample shows you singing, dancing, or another role.',
            'files.*.max' => 'Each photo or video must be 500 MB or smaller.',
            'files.*.mimetypes' => 'One of your files is not a supported photo or video format.',
        ]);

        $items = $profile->portfolios()->whereIn('id', $validated['item_ids'])->get();

        abort_if($items->isEmpty(), 404);

        $removeIds = collect($validated['remove_ids'] ?? []);
        $eventName = $validated['event_name'] ?? null;
        $caption = $validated['caption'] ?? null;
        $categoryIds = array_values(array_unique(array_map('intval', $validated['category_ids'])));
        $newFiles = $this->uploadedFiles($request);
        $supabase = ($removeIds->isNotEmpty() || $newFiles !== [])
            ? new SupabaseStorageService()
            : null;

        // Self-heal legacy posts (grouped only by timestamp) onto a real shared batch_key.
        $batchKey = $items->first()->batch_key ?? Str::uuid()->toString();

        $remaining = 0;

        foreach ($items as $item) {
            if ($removeIds->contains($item->id)) {
                $supabase->delete('performer-files', $item->file_path);
                $item->delete();
                continue;
            }

            $item->update([
                'event_name' => $eventName,
                'caption' => $caption,
                'category_ids' => $categoryIds,
                'batch_key' => $batchKey,
            ]);
            $remaining++;
        }

        foreach ($newFiles as $file) {
            [$type, $supabaseType] = $this->fileTypes($file);
            $path = $supabase->upload($file, 'performer-files', $supabaseType, Auth::id());

            $profile->portfolios()->create([
                'batch_key' => $batchKey,
                'type' => $type,
                'file_path' => $path,
                'event_name' => $eventName,
                'caption' => $caption,
                'category_ids' => $categoryIds,
            ]);

            $remaining++;
        }

        if ($remaining === 0) {
            return redirect()
                ->route('performer.portfolio.index')
                ->with('success', 'Post removed.');
        }

        return back()->with('success', 'Post updated.');
    }

    public function destroy(int $portfolio): RedirectResponse
    {
        $item = Portfolio::find($portfolio);

        // Already removed (e.g. a duplicate/stale request) — treat as success rather than 404.
        if (! $item) {
            return back()->with('success', 'Portfolio item removed.');
        }

        abort_unless($item->performer_profile_id === Auth::user()->performerProfile->id, 403);
        (new SupabaseStorageService())->delete('performer-files', $item->file_path);
        $item->delete();

        return back()->with('success', 'Portfolio item removed.');
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function uploadedFiles(Request $request): array
    {
        $files = $request->file('files', []);

        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        return array_values(array_filter(
            $files,
            fn ($file) => $file instanceof UploadedFile && $file->isValid()
        ));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function fileTypes(UploadedFile $file): array
    {
        if (str_starts_with((string) $file->getMimeType(), 'video/')) {
            return ['video', 'portfolio_video'];
        }

        return ['photo', 'portfolio_image'];
    }
}

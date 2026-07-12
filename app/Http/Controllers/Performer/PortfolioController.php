<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Services\SupabaseStorageService;
use App\Support\PortfolioFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $portfolios = Auth::user()->performerProfile->portfolios()->latest()->get();

        $portfolioGroups = PortfolioFeed::groupItems($portfolios);

        return view('performer.portfolio.index', compact('portfolioGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile;

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:512000', // 500 MB per file (kilobytes) — Supabase project's storage size ceiling
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/x-msvideo',
            ],
            'caption' => ['nullable', 'string', 'max:2000'],
        ], [
            'files.*.max' => 'Each photo or video must be 500 MB or smaller.',
            'files.*.mimetypes' => 'One of your files is not a supported photo or video format.',
        ]);

        $caption = $validated['caption'] ?? null;
        $uploaded = 0;
        $supabase = new SupabaseStorageService();
        $batchKey = Str::uuid()->toString();

        foreach ($request->file('files', []) as $file) {
            $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'photo';
            $supabaseType = $type === 'video' ? 'portfolio_video' : 'portfolio_image';
            $path = $supabase->upload($file, 'performer-files', $supabaseType, Auth::id());

            $profile->portfolios()->create([
                'batch_key' => $batchKey,
                'type' => $type,
                'file_path' => $path,
                'caption' => $caption,
            ]);

            $uploaded++;
        }

        $message = $uploaded === 1
            ? 'Portfolio item uploaded.'
            : "{$uploaded} portfolio items uploaded.";

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
            'caption' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array'],
            'files.*' => [
                'file',
                'max:512000', // 500 MB per file (kilobytes) — Supabase project's storage size ceiling
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/x-msvideo',
            ],
        ], [
            'files.*.max' => 'Each photo or video must be 500 MB or smaller.',
            'files.*.mimetypes' => 'One of your files is not a supported photo or video format.',
        ]);

        $items = $profile->portfolios()->whereIn('id', $validated['item_ids'])->get();

        abort_if($items->isEmpty(), 404);

        $removeIds = collect($validated['remove_ids'] ?? []);
        $caption = $validated['caption'] ?? null;
        $supabase = new SupabaseStorageService();

        // Self-heal legacy posts (grouped only by timestamp) onto a real shared batch_key.
        $batchKey = $items->first()->batch_key ?? Str::uuid()->toString();

        $remaining = 0;

        foreach ($items as $item) {
            if ($removeIds->contains($item->id)) {
                $supabase->delete('performer-files', $item->file_path);
                $item->delete();
                continue;
            }

            $item->update(['caption' => $caption, 'batch_key' => $batchKey]);
            $remaining++;
        }

        foreach ($request->file('files', []) as $file) {
            $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'photo';
            $supabaseType = $type === 'video' ? 'portfolio_video' : 'portfolio_image';
            $path = $supabase->upload($file, 'performer-files', $supabaseType, Auth::id());

            $profile->portfolios()->create([
                'batch_key' => $batchKey,
                'type' => $type,
                'file_path' => $path,
                'caption' => $caption,
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
}

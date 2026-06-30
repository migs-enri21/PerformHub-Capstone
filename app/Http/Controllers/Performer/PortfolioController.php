<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Support\PortfolioFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                'max:51200',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/x-msvideo',
            ],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $caption = $validated['caption'] ?? null;
        $uploaded = 0;

        foreach ($request->file('files', []) as $file) {
            $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'photo';
            $path = $file->store('portfolios', 'public');

            $profile->portfolios()->create([
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
            ->route('performer.dashboard')
            ->with('success', $message.' Your post is now on the community feed.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->performer_profile_id === Auth::user()->performerProfile->id, 403);
        Storage::disk('public')->delete($portfolio->file_path);
        $portfolio->delete();

        return back()->with('success', 'Portfolio item removed.');
    }
}

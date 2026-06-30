<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
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

        return view('performer.portfolio.index', compact('portfolios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile;

        $validated = $request->validate([
            'type' => ['required', 'in:photo,video'],
            'file' => ['required', 'file', 'max:51200'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $request->file('file')->store('portfolios', 'public');

        $profile->portfolios()->create([
            'type' => $validated['type'],
            'file_path' => $path,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Portfolio item uploaded.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->performer_profile_id === Auth::user()->performerProfile->id, 403);
        Storage::disk('public')->delete($portfolio->file_path);
        $portfolio->delete();

        return back()->with('success', 'Portfolio item removed.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function showProfile(): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function showVerification(): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function storeVerification(Request $request): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function showComplete(): RedirectResponse
    {
        return redirect(Auth::user()->dashboardRoute());
    }

    public function dismissBanner(Request $request): RedirectResponse
    {
        $request->session()->put('onboarding_banner_dismissed', true);

        return back();
    }
}

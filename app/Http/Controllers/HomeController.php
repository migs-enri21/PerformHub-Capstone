<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PerformerProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->where('is_active', true)->get();
        $featuredPerformers = PerformerProfile::query()
            ->with(['user', 'categories'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->where('is_verified', true))
            ->where('is_verified_badge', true)
            ->latest()
            ->limit(6)
            ->get();

        return view('landing', compact('categories', 'featuredPerformers'));
    }
}

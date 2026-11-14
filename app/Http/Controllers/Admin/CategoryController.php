<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $status = request('status');
        $sort = request('sort', 'name_asc');

        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        }

        if ($status !== null) {
            $query->where('is_active', $status === 'active');
        }

        $categories = $this->applySort($query, $sort)->paginate(15)->withQueryString();

        $eventTypesQuery = EventType::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%"))
            ->when($status !== null, fn ($query) => $query->where('is_active', $status === 'active'));
        $eventTypes = $this->applySort($eventTypesQuery, $sort)
            ->paginate(15, ['*'], 'event_types_page')
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'eventTypes', 'search', 'status'));
    }

    private function applySort($query, string $sort)
    {
        return match ($sort) {
            'latest' => $query->latest(),
            'name_desc' => $query->orderByRaw('LOWER(name) DESC'),
            default => $query->orderByRaw('LOWER(name) ASC'),
        };
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Category::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_active' => true,
        ]);

        return back()
            ->with('success', 'Category created.')
            ->with('created_category', [
                'id' => $category->id,
                'name' => $category->name,
            ]);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function show(Category $category): View
    {
        return view('admin.categories.show', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $isActive = $request->boolean('is_active');
        $validated['is_active'] = $isActive;

        DB::transaction(function () use ($category, $validated, $isActive): void {
            $category->update($validated);

            if (! $isActive) {
                $category->genres()->update(['is_active' => false]);
            }
        });

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $isActive = ! $category->is_active;

        DB::transaction(function () use ($category, $isActive): void {
            $category->update(['is_active' => $isActive]);

            if (! $isActive) {
                $category->genres()->update(['is_active' => false]);
            }
        });

        $status = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category $status.");
    }
}

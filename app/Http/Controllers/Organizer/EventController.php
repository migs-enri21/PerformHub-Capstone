<?php

namespace App\Http\Controllers\Organizer;

use App\Models\Category;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        return view('organizer.events.create', compact('categories'));
    }
}
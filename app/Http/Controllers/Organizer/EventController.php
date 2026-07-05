<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function create()
    {
        return view('organizer.events.create');
    }
}
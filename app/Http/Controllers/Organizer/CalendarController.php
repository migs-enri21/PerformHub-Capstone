<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    public function index()
    {
        $profile = auth()->user()->organizerProfile;

        return view('organizer.calendar.index', compact('profile'));
    }
}
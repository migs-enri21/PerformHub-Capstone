<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;

class EventHistoryController extends Controller
{
    public function index()
    {
        return view('organizer.history.index');
    }
}


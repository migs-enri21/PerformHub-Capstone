<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventHistoryController extends Controller
{
    public function index(): View
    {
        $events = Event::where('organizer_id', Auth::id())
            ->where(function ($query) {
                $query->whereDate('event_date', '<', today())
                    ->orWhereIn('status', ['Completed', 'Cancelled']);
            })
            ->latest('event_date')
            ->paginate(10);

        return view('organizer.history.index', compact('events'));
    }
}

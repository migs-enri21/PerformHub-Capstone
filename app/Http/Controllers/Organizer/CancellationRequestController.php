<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CancellationRequestController extends Controller
{
    public function index(): View
    {
        $requests = Booking::where('organizer_id', Auth::id())
            ->whereNotNull('cancel_requested_at')
            ->whereIn('status', ['accepted', 'completed'])
            ->with('performer.performerProfile')
            ->orderByDesc('cancel_requested_at')
            ->get();

        return view('organizer.cancellation-requests.index', compact('requests'));
    }
}

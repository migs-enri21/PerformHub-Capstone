<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Interview;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function bookings(): View
    {
        $bookings = Booking::with(['organizer', 'performer', 'interview'])->latest()->paginate(20);

        return view('admin.monitoring.bookings', compact('bookings'));
    }

    public function interviews(): View
    {
        $interviews = Interview::with(['booking', 'organizer', 'performer'])->latest()->paginate(20);

        return view('admin.monitoring.interviews', compact('interviews'));
    }
}

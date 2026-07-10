<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function bookings(): View
    {
        $bookings = Booking::with(['organizer', 'performer'])->latest()->paginate(20);

        return view('admin.monitoring.bookings', compact('bookings'));
    }
}

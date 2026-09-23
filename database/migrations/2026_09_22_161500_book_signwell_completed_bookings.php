<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $signed = Booking::query()
            ->where('status', 'accepted')
            ->get()
            ->filter(fn (Booking $booking) => $booking->isSigned());

        foreach ($signed as $booking) {
            $booking->markCompletedFromSignature();
        }
    }

    public function down(): void
    {
        // Signed bookings stay booked.
    }
};

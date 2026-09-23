<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $signed = Booking::query()
            ->where('status', 'accepted')
            ->whereNotNull('signed_contract_path')
            ->orderBy('id')
            ->get();

        foreach ($signed as $booking) {
            $booking->markCompletedFromSignature();
        }
    }

    public function down(): void
    {
        // Signed bookings stay completed.
    }
};

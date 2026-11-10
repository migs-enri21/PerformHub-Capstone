<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('event_date');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('event_date');
        });

        $events = DB::table('events')->select('id', 'event_date', 'start_time', 'end_time')->get();

        foreach ($events as $event) {
            $endDate = $event->event_date;

            if ($event->start_time && $event->end_time && $event->end_time <= $event->start_time) {
                $endDate = Carbon::parse($event->event_date)->addDay()->toDateString();
            }

            DB::table('events')->where('id', $event->id)->update(['end_date' => $endDate]);
        }

        $bookings = DB::table('bookings')->select('id', 'event_date', 'event_time', 'end_time')->get();

        foreach ($bookings as $booking) {
            $endDate = $booking->event_date;

            if ($booking->event_time && $booking->end_time && $booking->end_time <= $booking->event_time) {
                $endDate = Carbon::parse($booking->event_date)->addDay()->toDateString();
            }

            DB::table('bookings')->where('id', $booking->id)->update(['end_date' => $endDate]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};

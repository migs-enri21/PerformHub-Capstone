<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS cancel_reason TEXT NULL");
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS cancel_requested_at TIMESTAMP NULL");
        DB::statement("ALTER TYPE bookings_status_enum ADD VALUE IF NOT EXISTS 'cancelled'");
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }
            if (Schema::hasColumn('bookings', 'cancel_requested_at')) {
                $table->dropColumn('cancel_requested_at');
            }
        });
    }
};
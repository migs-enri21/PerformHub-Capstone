<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('assigned_start_date')->nullable()->after('end_date');
            $table->date('assigned_end_date')->nullable()->after('assigned_start_date');
        });

        DB::table('bookings')->update([
            'assigned_start_date' => DB::raw('event_date'),
            'assigned_end_date' => DB::raw('end_date'),
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['assigned_start_date', 'assigned_end_date']);
        });
    }
};

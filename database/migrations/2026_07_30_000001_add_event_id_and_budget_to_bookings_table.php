<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('performer_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('bookings', 'budget')) {
                $table->decimal('budget', 10, 2)->nullable()->after('notes');
            }

            if (! Schema::hasColumn('bookings', 'end_time')) {
                $table->time('end_time')->nullable()->after('event_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }

            if (Schema::hasColumn('bookings', 'budget')) {
                $table->dropColumn('budget');
            }

            if (Schema::hasColumn('bookings', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};

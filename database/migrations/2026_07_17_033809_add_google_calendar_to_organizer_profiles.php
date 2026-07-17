<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {

            $table->boolean('google_calendar_connected')->default(false)->after('website');
            $table->string('google_calendar_id')->nullable()->after('google_calendar_connected');
            $table->text('google_refresh_token')->nullable()->after('google_calendar_id');
            $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
            $table->timestamp('google_calendar_synced_at')->nullable()->after('google_token_expires_at');

        });
    }

    public function down(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {

            $table->dropColumn([
                'google_calendar_connected',
                'google_calendar_id',
                'google_refresh_token',
                'google_token_expires_at',
                'google_calendar_synced_at',
            ]);

        });
    }
};
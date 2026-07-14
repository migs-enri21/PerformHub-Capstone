<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->string('banner_photo')->nullable()->after('profile_photo');
            // Vertical focal point (0 = top of photo, 100 = bottom) used as the
            // CSS background-position-y when cropping the wide banner strip.
            $table->unsignedTinyInteger('banner_position_y')->default(50)->after('banner_photo');
        });
    }

    public function down(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->dropColumn(['banner_photo', 'banner_position_y']);
        });
    }
};

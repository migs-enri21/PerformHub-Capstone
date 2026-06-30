<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->string('region')->nullable()->after('location');
            $table->string('city')->nullable()->after('region');
            $table->string('barangay')->nullable()->after('city');
        });

        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->string('region')->nullable()->after('location');
            $table->string('city')->nullable()->after('region');
            $table->string('barangay')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropColumn(['region', 'city', 'barangay']);
        });

        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->dropColumn(['region', 'city', 'barangay']);
        });
    }
};

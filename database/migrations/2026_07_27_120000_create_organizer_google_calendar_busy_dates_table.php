<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizer_google_calendar_busy_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_profile_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('summary')->nullable();
            $table->timestamps();

            $table->unique(['organizer_profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizer_google_calendar_busy_dates');
    }
};

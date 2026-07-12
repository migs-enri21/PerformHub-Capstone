<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('event_type_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->text('description')->nullable();
        $table->date('event_date');
        $table->time('start_time');
        $table->time('end_time')->nullable();
        $table->string('venue');
        $table->decimal('budget', 10, 2)->nullable();
        $table->integer('performers_needed')->default(1);
        $table->string('status')->default('Draft');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

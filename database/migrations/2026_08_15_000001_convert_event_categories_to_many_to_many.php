<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['event_id', 'category_id']);
        });

        DB::table('events')
            ->whereNotNull('preferred_category_id')
            ->select('id', 'preferred_category_id')
            ->orderBy('id')
            ->each(function ($event) {
                DB::table('event_category')->insert([
                    'event_id' => $event->id,
                    'category_id' => $event->preferred_category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('preferred_category_id')
                ->nullable()
                ->after('event_type_id')
                ->constrained('categories')
                ->nullOnDelete();
        });

        DB::table('event_category')
            ->select('event_id', 'category_id')
            ->orderBy('id')
            ->each(function ($eventCategory) {
                DB::table('events')
                    ->where('id', $eventCategory->event_id)
                    ->whereNull('preferred_category_id')
                    ->update(['preferred_category_id' => $eventCategory->category_id]);
            });

        Schema::dropIfExists('event_category');
    }
};

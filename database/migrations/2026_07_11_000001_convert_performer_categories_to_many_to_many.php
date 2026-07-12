<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performer_profile_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['performer_profile_id', 'category_id']);
        });

        // Carry forward each performer's existing single category into the pivot table.
        DB::table('performer_profiles')
            ->whereNotNull('category_id')
            ->select('id', 'category_id')
            ->orderBy('id')
            ->each(function ($profile) {
                DB::table('performer_profile_category')->insert([
                    'performer_profile_id' => $profile->id,
                    'category_id' => $profile->category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('genre')->constrained()->nullOnDelete();
        });

        DB::table('performer_profile_category')
            ->select('performer_profile_id', 'category_id')
            ->orderBy('id')
            ->each(function ($pivot) {
                DB::table('performer_profiles')
                    ->where('id', $pivot->performer_profile_id)
                    ->whereNull('category_id')
                    ->update(['category_id' => $pivot->category_id]);
            });

        Schema::dropIfExists('performer_profile_category');
    }
};

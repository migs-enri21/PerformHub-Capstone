<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $therapist = DB::table('categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['therapist'])
                    ->orWhere('slug', 'therapist');
            })
            ->first();

        if (! $therapist) {
            return;
        }

        $id = (int) $therapist->id;

        if (Schema::hasTable('performer_profile_category')) {
            DB::table('performer_profile_category')->where('category_id', $id)->delete();
        }

        if (Schema::hasTable('event_category')) {
            DB::table('event_category')->where('category_id', $id)->delete();
        }

        if (Schema::hasColumn('genres', 'category_id')) {
            DB::table('genres')->where('category_id', $id)->update(['category_id' => null]);
        }

        if (Schema::hasColumn('specialties', 'category_id')) {
            DB::table('specialties')->where('category_id', $id)->update(['category_id' => null]);
        }

        if (Schema::hasTable('portfolios') && Schema::hasColumn('portfolios', 'category_ids')) {
            $portfolios = DB::table('portfolios')->whereNotNull('category_ids')->get(['id', 'category_ids']);

            foreach ($portfolios as $portfolio) {
                $ids = is_array($portfolio->category_ids)
                    ? $portfolio->category_ids
                    : json_decode((string) $portfolio->category_ids, true);

                if (! is_array($ids)) {
                    continue;
                }

                $filtered = array_values(array_filter($ids, fn ($categoryId) => (int) $categoryId !== $id));

                if ($filtered === $ids) {
                    continue;
                }

                DB::table('portfolios')->where('id', $portfolio->id)->update([
                    'category_ids' => json_encode($filtered),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('categories')->where('id', $id)->delete();
    }

    public function down(): void
    {
        $exists = DB::table('categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['therapist'])
                    ->orWhere('slug', 'therapist');
            })
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('categories')->insert([
            'name' => 'Therapist',
            'slug' => 'therapist',
            'description' => null,
            'icon' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};

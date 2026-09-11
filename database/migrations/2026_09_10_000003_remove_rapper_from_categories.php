<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $rapper = DB::table('categories')->whereRaw('LOWER(name) = ?', ['rapper'])->first();

        if (! $rapper) {
            return;
        }

        $singer = DB::table('categories')
            ->whereRaw('LOWER(name) in (?, ?)', ['singer', 'singers'])
            ->first();

        if (! $singer) {
            DB::table('categories')->where('id', $rapper->id)->update([
                'name' => 'Singer',
                'slug' => 'singer',
                'description' => 'Vocal performers',
                'updated_at' => now(),
            ]);

            return;
        }

        $this->reassignPivots('performer_profile_category', 'performer_profile_id', $rapper->id, $singer->id);
        $this->reassignPivots('event_category', 'event_id', $rapper->id, $singer->id);

        $profileIds = DB::table('performer_profile_category')
            ->where('category_id', $rapper->id)
            ->pluck('performer_profile_id');

        foreach ($profileIds as $profileId) {
            $profile = DB::table('performer_profiles')->where('id', $profileId)->first();

            if (! $profile) {
                continue;
            }

            $updates = [];

            if ($profile->specialty === null || $profile->specialty === '') {
                $updates['specialty'] = 'Rap Vocals';
            }

            if ($profile->genre === null || $profile->genre === '') {
                $updates['genre'] = 'Rap';
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('performer_profiles')->where('id', $profileId)->update($updates);
            }
        }

        DB::table('performer_profile_category')->where('category_id', $rapper->id)->delete();

        if (Schema::hasTable('event_category')) {
            DB::table('event_category')->where('category_id', $rapper->id)->delete();
        }

        DB::table('categories')->where('id', $rapper->id)->delete();
    }

    public function down(): void
    {
        $exists = DB::table('categories')->whereRaw('LOWER(name) = ?', ['rapper'])->exists();

        if ($exists) {
            return;
        }

        DB::table('categories')->insert([
            'name' => 'Rapper',
            'slug' => Str::slug('Rapper'),
            'description' => 'Hip-hop and rap vocalists',
            'icon' => 'fa-microphone',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function reassignPivots(string $table, string $ownerColumn, int $fromId, int $toId): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $ownerIds = DB::table($table)->where('category_id', $fromId)->pluck($ownerColumn);

        foreach ($ownerIds as $ownerId) {
            $already = DB::table($table)
                ->where($ownerColumn, $ownerId)
                ->where('category_id', $toId)
                ->exists();

            if ($already) {
                continue;
            }

            DB::table($table)->insert([
                $ownerColumn => $ownerId,
                'category_id' => $toId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};

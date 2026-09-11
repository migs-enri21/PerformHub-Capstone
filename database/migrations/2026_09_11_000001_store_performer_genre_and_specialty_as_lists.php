<?php

use App\Support\OptionList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('performer_profiles')->select('id', 'genre', 'specialty')->get();

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->json('genre_values')->nullable();
            $table->json('specialty_values')->nullable();
        });

        foreach ($rows as $row) {
            $genres = OptionList::wrap($row->genre);
            $specialties = OptionList::wrap($row->specialty);

            DB::table('performer_profiles')->where('id', $row->id)->update([
                'genre_values' => $genres === [] ? null : json_encode(array_values($genres)),
                'specialty_values' => $specialties === [] ? null : json_encode(array_values($specialties)),
            ]);
        }

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropColumn(['genre', 'specialty']);
        });

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->renameColumn('genre_values', 'genre');
            $table->renameColumn('specialty_values', 'specialty');
        });
    }

    public function down(): void
    {
        $rows = DB::table('performer_profiles')->select('id', 'genre', 'specialty')->get();

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->string('genre_text')->nullable();
            $table->string('specialty_text')->nullable();
        });

        foreach ($rows as $row) {
            $genres = OptionList::wrap(is_string($row->genre) ? $row->genre : json_encode($row->genre));
            $specialties = OptionList::wrap(is_string($row->specialty) ? $row->specialty : json_encode($row->specialty));

            DB::table('performer_profiles')->where('id', $row->id)->update([
                'genre_text' => $genres[0] ?? null,
                'specialty_text' => $specialties[0] ?? null,
            ]);
        }

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropColumn(['genre', 'specialty']);
        });

        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->renameColumn('genre_text', 'genre');
            $table->renameColumn('specialty_text', 'specialty');
        });
    }
};

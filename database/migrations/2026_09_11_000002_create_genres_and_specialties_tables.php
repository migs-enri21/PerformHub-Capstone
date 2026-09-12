<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedTable('genres', config('genres.options', []));
        $this->seedTable('specialties', config('specialties.options', []));
    }

    public function down(): void
    {
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('genres');
    }

    /**
     * @param  array<int, string>  $names
     */
    private function seedTable(string $table, array $names): void
    {
        $now = now();

        foreach ($names as $name) {
            if (! is_string($name) || $name === '') {
                continue;
            }

            $slug = Str::slug($name) ?: 'item';
            $base = $slug;
            $counter = 1;

            while (DB::table($table)->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$counter}";
                $counter++;
            }

            DB::table($table)->insert([
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('genres', 'category_id')) {
            Schema::table('genres', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('specialties', 'category_id')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('genres', 'category_id')) {
            Schema::table('genres', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }

        if (Schema::hasColumn('specialties', 'category_id')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }
};

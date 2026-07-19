<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->foreignId('preferred_category_id')
                ->nullable()
                ->after('event_type_id')
                ->constrained('categories')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->dropConstrainedForeignId('preferred_category_id');

        });
    }
};
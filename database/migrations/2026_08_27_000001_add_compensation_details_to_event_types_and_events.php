<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table): void {
            $table->string('compensation_type')->default('fixed')->after('description');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->decimal('first_prize', 10, 2)->nullable()->after('budget');
            $table->decimal('second_prize', 10, 2)->nullable()->after('first_prize');
            $table->decimal('third_prize', 10, 2)->nullable()->after('second_prize');
            $table->decimal('rate_per_hour', 10, 2)->nullable()->after('third_prize');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['first_prize', 'second_prize', 'third_prize', 'rate_per_hour']);
        });

        Schema::table('event_types', function (Blueprint $table): void {
            $table->dropColumn('compensation_type');
        });
    }
};

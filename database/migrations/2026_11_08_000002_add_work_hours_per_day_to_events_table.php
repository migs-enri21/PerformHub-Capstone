<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedTinyInteger('work_hours_per_day')->nullable()->after('rate_per_hour');
        });

        DB::table('events')
            ->where('compensation_type', 'hourly')
            ->update(['work_hours_per_day' => 8]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('work_hours_per_day');
        });
    }
};

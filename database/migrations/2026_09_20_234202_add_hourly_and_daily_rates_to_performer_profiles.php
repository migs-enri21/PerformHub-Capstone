<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->decimal('rate_per_hour', 10, 2)->nullable()->after('rate');
            $table->decimal('rate_per_day', 10, 2)->nullable()->after('rate_per_hour');
        });

        DB::table('performer_profiles')
            ->whereNotNull('rate')
            ->update([
                'rate_per_day' => DB::raw('rate'),
            ]);
    }

    public function down(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropColumn(['rate_per_hour', 'rate_per_day']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('caption')->nullable()->after('file_path');
        });

        if (Schema::hasColumn('portfolios', 'title')) {
            DB::table('portfolios')->whereNotNull('title')->update([
                'caption' => DB::raw('title'),
            ]);
        }

        Schema::table('portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('portfolios', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('portfolios', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('title')->nullable()->after('file_path');
            $table->text('description')->nullable()->after('title');
        });

        DB::table('portfolios')->whereNotNull('caption')->update([
            'title' => DB::raw('caption'),
        ]);

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};

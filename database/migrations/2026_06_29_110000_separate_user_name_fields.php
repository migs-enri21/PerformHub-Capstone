<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'name')) {
            return;
        }

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            if (! empty($user->first_name)) {
                return;
            }

            $parts = preg_split('/\s+/', trim((string) $user->name), 2);

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? 'User',
                'last_name' => $parts[1] ?? '',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'name')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim($user->first_name.' '.$user->last_name),
            ]);
        });
    }
};

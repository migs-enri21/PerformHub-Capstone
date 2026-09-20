<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizer_profiles')
            ->where('organization_type', 'company')
            ->update(['organization_type' => 'agency']);

        DB::table('organizer_profiles')
            ->where('organization_type', 'individual')
            ->update(['organization_type' => 'freelancer']);

        DB::table('organizer_profiles')
            ->where('organization_type', 'nonprofit')
            ->update(['organization_type' => 'agency']);
    }

    public function down(): void
    {
        DB::table('organizer_profiles')
            ->where('organization_type', 'agency')
            ->update(['organization_type' => 'company']);

        DB::table('organizer_profiles')
            ->where('organization_type', 'freelancer')
            ->update(['organization_type' => 'individual']);
    }
};
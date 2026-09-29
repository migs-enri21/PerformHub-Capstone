<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $enumExists = DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'bookings_status_enum'");

        if ($enumExists) {
            DB::statement("ALTER TYPE bookings_status_enum ADD VALUE IF NOT EXISTS 'expired'");
        }

        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status::text = ANY (ARRAY['pending','interview_scheduled','accepted','rejected','completed','cancelled','expired']::text[]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status::text = ANY (ARRAY['pending','interview_scheduled','accepted','rejected','completed']::text[]))");
    }
};

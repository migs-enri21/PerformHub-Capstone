<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('verification_documents', 'government_id_type')) {
                $table->string('government_id_type')->nullable()->after('document_type');
            }
            if (! Schema::hasColumn('verification_documents', 'government_id_other')) {
                $table->string('government_id_other')->nullable()->after('government_id_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('verification_documents', function (Blueprint $table) {
            if (Schema::hasColumn('verification_documents', 'government_id_other')) {
                $table->dropColumn('government_id_other');
            }
            if (Schema::hasColumn('verification_documents', 'government_id_type')) {
                $table->dropColumn('government_id_type');
            }
        });
    }
};

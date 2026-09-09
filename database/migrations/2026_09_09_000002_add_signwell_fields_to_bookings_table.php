<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('signwell_document_id')->nullable()->after('signed_contract_uploaded_at');
            $table->string('signwell_status')->nullable()->after('signwell_document_id');
            $table->timestamp('signwell_sent_at')->nullable()->after('signwell_status');
            $table->timestamp('signwell_completed_at')->nullable()->after('signwell_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'signwell_document_id',
                'signwell_status',
                'signwell_sent_at',
                'signwell_completed_at',
            ]);
        });
    }
};

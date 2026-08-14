<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('signed_contract_path')->nullable()->after('contract_path');
            $table->timestamp('signed_contract_uploaded_at')->nullable()->after('signed_contract_path');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['signed_contract_path', 'signed_contract_uploaded_at']);
        });
    }
};

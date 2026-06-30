<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'onboarding_step')) {
                $table->unsignedTinyInteger('onboarding_step')->default(0)->after('is_active');
            }
        });

        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->string('organization_type')->nullable()->after('organization_name');
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_documents');

        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->dropColumn('organization_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'phone', 'onboarding_step']);
        });
    }
};

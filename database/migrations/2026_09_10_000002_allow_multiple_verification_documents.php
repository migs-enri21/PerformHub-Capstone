<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getIndexes('verification_documents') as $index) {
            $columns = $index['columns'] ?? [];
            $isUnique = $index['unique'] ?? false;
            $name = $index['name'] ?? null;

            if ($isUnique && $name && $columns === ['user_id', 'document_type']) {
                Schema::table('verification_documents', function (Blueprint $table) use ($name) {
                    $table->dropUnique($name);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('verification_documents', function (Blueprint $table) {
            $table->unique(['user_id', 'document_type']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('performer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stage_name');
            $table->text('bio')->nullable();
            $table->string('genre')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('rate', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_twitter')->nullable();
            $table->boolean('is_verified_badge')->default(false);
            $table->timestamps();
        });

        Schema::create('organizer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('organization_name');
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performer_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['photo', 'video']);
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('availability_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performer_profile_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['performer_profile_id', 'date'], 'availability_unique');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('performer_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_name');
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('venue')->nullable();
            $table->text('requirements')->nullable();
            $table->unsignedSmallInteger('duration_hours')->nullable();
            $table->enum('status', [
                'pending',
                'interview_scheduled',
                'accepted',
                'rejected',
                'completed',
            ])->default('pending');
            $table->string('contract_path')->nullable();
            $table->timestamp('contract_confirmed_at')->nullable();
            $table->boolean('performer_confirmed_contract')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('performer_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('jitsi_room_id');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('availability_schedules');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('organizer_profiles');
        Schema::dropIfExists('performer_profiles');
        Schema::dropIfExists('categories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('social_facebook_followers')->nullable()->after('social_facebook');
            $table->unsignedBigInteger('social_instagram_followers')->nullable()->after('social_instagram');
            $table->unsignedBigInteger('social_youtube_subscribers')->nullable()->after('social_youtube');
            $table->unsignedBigInteger('social_tiktok_followers')->nullable()->after('social_tiktok');
            $table->unsignedBigInteger('social_twitter_followers')->nullable()->after('social_twitter');
        });
    }

    public function down(): void
    {
        Schema::table('performer_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'social_facebook_followers',
                'social_instagram_followers',
                'social_youtube_subscribers',
                'social_tiktok_followers',
                'social_twitter_followers',
            ]);
        });
    }
};

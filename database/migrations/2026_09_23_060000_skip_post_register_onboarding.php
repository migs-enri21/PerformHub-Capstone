<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->where('onboarding_step', '<', User::ONBOARDING_COMPLETE)
            ->whereIn('role', [User::ROLE_PERFORMER, User::ROLE_ORGANIZER])
            ->update(['onboarding_step' => User::ONBOARDING_COMPLETE]);
    }

    public function down(): void
    {
        // Registration already collected this data.
    }
};

<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->where('role', User::ROLE_PERFORMER)
            ->where('is_verified', false)
            ->where('onboarding_step', User::ONBOARDING_COMPLETE)
            ->update(['onboarding_step' => User::ONBOARDING_PROFILE]);
    }

    public function down(): void
    {
        // Keep onboarding progress changes when rolling back this data fix.
    }
};
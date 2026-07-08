<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EventType;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@performhub.test',
            'password' => 'password',
            'role' => 'admin',
            'is_verified' => true,
            'is_active' => true,
            'onboarding_step' => User::ONBOARDING_COMPLETE,
        ]);

        $categories = [
            ['name' => 'Musicians', 'icon' => 'fa-music', 'description' => 'Bands, solo artists, and instrumentalists'],
            ['name' => 'Dancers', 'icon' => 'fa-person-running', 'description' => 'Contemporary, hip-hop, and cultural dance'],
            ['name' => 'Singers', 'icon' => 'fa-microphone', 'description' => 'Vocal performers and choirs'],
            ['name' => 'Magicians', 'icon' => 'fa-hat-wizard', 'description' => 'Illusionists and close-up magic'],
            ['name' => 'Comedians', 'icon' => 'fa-face-laugh', 'description' => 'Stand-up and emcees'],
            ['name' => 'DJs', 'icon' => 'fa-compact-disc', 'description' => 'Club and event DJs'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                ...$cat,
                'slug' => Str::slug($cat['name']),
                'is_active' => true,
            ]);
        }

        $eventTypes = [
            'Music Festival',
            'Concert',
            'Cultural Festival',
            'Food Festival',
            'Arts Festival',
            'Corporate Convention',
            'Trade Expo / Expo',
            'Product Launch',
            'Company Anniversary',
            'Awarding Ceremony',
            'Charity Gala',
            'Fundraising Event',
            'University Foundation Day',
            'Intramurals Opening & Closing Ceremony',
            'Graduation Ceremony',
            'Sports Tournament',
            'Marathon / Fun Run',
            'Esports Tournament',
            'Fashion Show',
            'Beauty Pageant',
            'Tourism Festival',
            'City or Municipal Fiesta',
            'Government Celebration',
            'Independence Day Celebration',
            'New Year\'s Countdown Event',
            'Christmas Festival',
            'Halloween Festival',
            'Cultural Showcase',
            'Youth Summit',
            'Business Summit',
            'Technology Conference',
            'Innovation Expo',
            'Community Fair',
            'Livelihood Fair',
            'Agricultural Expo',
            'Auto Show',
            'Motor Show',
            'Cosplay Convention',
            'Comic Convention',
            'Gaming Convention',
        ];

        foreach ($eventTypes as $eventType) {
            EventType::create([
                'name' => $eventType,
                'slug' => Str::slug($eventType),
                'description' => null,
                'is_active' => true,
            ]);
        }

        $performer = User::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'username' => 'juandelacruz',
            'email' => 'performer@performhub.test',
            'password' => 'password',
            'role' => 'performer',
            'phone' => '+63 912 345 6789',
            'is_verified' => true,
            'is_active' => true,
            'onboarding_step' => User::ONBOARDING_COMPLETE,
        ]);

        PerformerProfile::create([
            'user_id' => $performer->id,
            'stage_name' => 'JDC Live',
            'bio' => 'Professional acoustic performer specializing in weddings and corporate events.',
            'genre' => 'Acoustic Pop',
            'category_id' => 1,
            'rate' => 15000,
            'location' => 'Ermita, Manila, Metro Manila (NCR)',
            'region' => 'Metro Manila (NCR)',
            'city' => 'Manila',
            'barangay' => 'Ermita',
            'is_verified_badge' => true,
            'social_instagram' => 'https://instagram.com',
            'social_youtube' => 'https://youtube.com',
        ]);

        $organizer = User::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'username' => 'mariasantos',
            'email' => 'organizer@performhub.test',
            'password' => 'password',
            'role' => 'organizer',
            'phone' => '+63 912 345 6789',
            'is_verified' => true,
            'is_active' => true,
            'onboarding_step' => User::ONBOARDING_COMPLETE,
        ]);

        OrganizerProfile::create([
            'user_id' => $organizer->id,
            'organization_name' => 'Santos Events Co.',
            'organization_type' => 'company',
            'bio' => 'Premier event planning company in Metro Manila.',
            'location' => 'Ermita, Manila, Metro Manila (NCR)',
            'region' => 'Metro Manila (NCR)',
            'city' => 'Manila',
            'barangay' => 'Ermita',
            'phone' => '+63 912 345 6789',
        ]);
    }
}

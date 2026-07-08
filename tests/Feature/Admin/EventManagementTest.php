<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_event_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk();
    }

    public function test_admin_can_create_an_event_from_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $performer = User::factory()->create(['role' => 'performer']);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'event_name' => 'Launch Party',
                'event_date' => now()->addDay()->toDateString(),
                'event_time' => '19:00',
                'venue' => 'Main Hall',
                'organizer_id' => $organizer->id,
                'performer_id' => $performer->id,
                'status' => 'pending',
                'requirements' => 'Sound system',
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('bookings', [
            'event_name' => 'Launch Party',
            'organizer_id' => $organizer->id,
            'performer_id' => $performer->id,
        ]);
    }
}

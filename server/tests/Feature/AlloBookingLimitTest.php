<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allo;
use App\Models\AlloSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AlloBookingLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_booking_limit_blocks_extra_booking(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $now = Carbon::now();

        $allo = Allo::query()->create([
            'title' => 'Allo test',
            'slug' => 'allo-test',
            'description' => 'Test allo',
            'status' => 'OPEN',
            'window_start_at' => $now->copy()->subHour(),
            'window_end_at' => $now->copy()->addDays(2),
            'slot_duration_minutes' => 60,
            'security_margin_minutes' => 0,
            'daily_booking_limit' => 1,
            'time_slots' => null,
            'created_by_id' => $admin->id,
            'updated_by_id' => null,
        ]);

        $allo->admins()->attach($admin->id);

        $slotStart = $now->copy()->addDay()->setTime(9, 0);

        $firstSlot = AlloSlot::query()->create([
            'allo_id' => $allo->id,
            'slot_start_at' => $slotStart,
            'slot_end_at' => $slotStart->copy()->addHour(),
            'status' => 'available',
            'capacity' => 1,
        ]);

        $secondSlot = AlloSlot::query()->create([
            'allo_id' => $allo->id,
            'slot_start_at' => $slotStart->copy()->addHour(),
            'slot_end_at' => $slotStart->copy()->addHours(2),
            'status' => 'available',
            'capacity' => 1,
        ]);

        $this->actingAs($user)
            ->postJson('/api/allos/bookings', [
                'allo_id' => $allo->id,
                'allo_slot_id' => $firstSlot->id,
            ])
            ->assertCreated();

        $this->actingAs($user)
            ->postJson('/api/allos/bookings', [
                'allo_id' => $allo->id,
                'allo_slot_id' => $secondSlot->id,
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('allo_usages', 1);
    }
}

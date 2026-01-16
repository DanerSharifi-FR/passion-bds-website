<?php

namespace Tests\Feature;

use App\Models\Allo;
use App\Models\AlloSlot;
use App\Models\AlloUsage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AlloUsageDoubleSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_usage_twice_keeps_original_timestamp(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));

        $role = Role::create([
            'name' => 'ROLE_GAMEMASTER',
            'description' => 'Test role',
        ]);

        $admin = User::factory()->create([
            'university_email' => 'admin@passion.test',
            'display_name' => 'Admin Allos',
        ]);
        $admin->roles()->attach($role->id);

        $student = User::factory()->create([
            'university_email' => 'student@passion.test',
            'display_name' => 'Étudiant Allos',
        ]);

        $allo = Allo::create([
            'title' => 'Massage express',
            'description' => 'Test allo',
            'status' => 'OPEN',
            'slot_duration_minutes' => 15,
            'security_margin_minutes' => 0,
        ]);

        $slot = AlloSlot::create([
            'allo_id' => $allo->id,
            'slot_start_at' => Carbon::parse('2026-01-01 11:00:00'),
            'slot_end_at' => Carbon::parse('2026-01-01 11:15:00'),
            'status' => 'available',
            'capacity' => 1,
        ]);

        $usage = AlloUsage::create([
            'allo_id' => $allo->id,
            'allo_slot_id' => $slot->id,
            'slot_start_at' => $slot->slot_start_at,
            'user_id' => $student->id,
            'status' => 'PENDING',
        ]);

        $this->actingAs($admin)
            ->putJson("/admin/api/allo-usages/{$usage->id}", ['status' => 'ACCEPTED'])
            ->assertOk();

        $firstAcceptedAt = $usage->fresh()->accepted_at;

        Carbon::setTestNow(Carbon::parse('2026-01-01 10:05:00'));

        $this->actingAs($admin)
            ->putJson("/admin/api/allo-usages/{$usage->id}", ['status' => 'ACCEPTED'])
            ->assertOk();

        $usage->refresh();

        $this->assertTrue($usage->accepted_at->eq($firstAcceptedAt));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Implementation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImplanVerificationModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_implan_verify_action_uses_confirmation_modal(): void
    {
        $admin = User::factory()->role('admin')->create();
        $lgu = User::factory()->role('lgu')->create();
        $implan = Implementation::query()->create([
            'lgu_user_id' => $lgu->id,
            'issues' => 'Road access support',
            'program' => 'Barangay access improvement',
            'target_areas' => [],
            'agencies' => [],
            'status' => 'for verification',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.implan.show', $implan))
            ->assertSuccessful()
            ->assertSee('data-bs-target="#verifyImplanModal"', false)
            ->assertSee('id="verifyImplanModal"', false)
            ->assertSee('verify-implan-content', false)
            ->assertSee('verify-implan-scroll-lock', false)
            ->assertSee("modal.addEventListener('show.bs.modal', lockPageScroll)", false)
            ->assertSee("modal.addEventListener('hidden.bs.modal', unlockPageScroll)", false)
            ->assertSee('Confirm verification', false)
            ->assertSee('Mark this implementation plan as verified?', false)
            ->assertSee('action="'.route('admin.implan.verify', $implan).'"', false)
            ->assertDontSee('onsubmit="return confirm', false);
    }
}

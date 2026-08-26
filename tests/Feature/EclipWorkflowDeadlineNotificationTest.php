<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipWorkflowDeadlineNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_deadline_command_notifies_assigned_processor_once_for_upcoming_and_overdue_steps(): void
    {
        Carbon::setTestNow('2026-08-24 10:00:00');
        [$case, $lswdo] = $this->caseWithLswdo();
        $upcoming = $case->workflowActivities()->create([
            'step_code' => '6A', 'phase' => 2, 'title' => 'Encode Files', 'status' => 'pending',
            'responsible_roles' => ['lswdo'], 'available_at' => now(), 'due_at' => now()->addHours(12),
        ]);
        $overdue = $case->workflowActivities()->create([
            'step_code' => '6B', 'phase' => 2, 'title' => 'Submit Endorsement', 'status' => 'ongoing',
            'responsible_roles' => ['lswdo'], 'available_at' => now()->subDays(2), 'due_at' => now()->subMinute(),
        ]);

        $this->artisan('eclip:mark-late-activities')->assertSuccessful();
        $this->artisan('eclip:mark-late-activities')->assertSuccessful();

        $this->assertSame('late', $overdue->fresh()->status);
        $this->assertCount(2, $lswdo->notifications()->get());
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $upcoming->id,
            'remarks' => 'Upcoming deadline notification sent.',
        ]);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $overdue->id,
            'remarks' => 'Overdue deadline notification sent.',
        ]);
    }

    private function caseWithLswdo(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Deadline Test Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-DEADLINE-001', 'firstname' => 'Synthetic', 'lastname' => 'Deadline',
            'municipality_id' => $municipality->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-DEADLINE-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::Eligible,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $lswdo->id, 'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id, 'assigned_at' => now(), 'is_active' => true,
        ]);

        return [$case, $lswdo];
    }
}

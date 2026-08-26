<?php

namespace Tests\Feature\Mblrc;

use App\Models\FormerRebel;
use App\Models\LswdoReferral;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_workspace_is_assignment_scoped_and_uses_real_summary_and_progress_data(): void
    {
        CarbonImmutable::setTestNow('2026-08-09 08:00:00');
        $municipality = Municipality::query()->create(['name' => 'Authorized Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $otherMblrc = User::factory()->role('mblrc')->create();
        $active = $this->enrollment($mblrc, 'FR-#ACTIVE', '2026-07-09', municipality: $municipality);
        $attention = $this->enrollment($mblrc, 'FR-#ATTENTION', '2026-05-09', municipality: $municipality);
        $completed = $this->enrollment($mblrc, 'FR-#COMPLETE', '2026-04-01', 'completed', $municipality);
        $completed->update(['integration_completed_at' => '2026-07-01']);
        $this->enrollment($otherMblrc, 'FR-#OUTSIDE', '2026-05-01', municipality: $municipality);

        LswdoReferral::query()->create([
            'referral_number' => 'LSWDO-TEST-001',
            'mblrc_enrollment_id' => $completed->id,
            'former_rebel_id' => $completed->former_rebel_id,
            'municipality_id' => $municipality->id,
            'created_by' => $mblrc->id,
            'status' => 'pending',
            'referred_at' => now(),
        ]);

        $response = $this->actingAs($mblrc)->get(route('mblrc.enrollments.index'));

        $response->assertOk()
            ->assertSee('Integration Monitoring')
            ->assertSee('module-title-icon', false)
            ->assertSee('mdi-progress-clock', false)
            ->assertSee('mdi-account-group', false)
            ->assertSee('mdi-progress-check', false)
            ->assertSee('mdi-check-decagram', false)
            ->assertSee('mdi-alert-circle', false)
            ->assertSee('start-modal-heading', false)
            ->assertSee('Begin the official three-month integration monitoring for an eligible FR/FVE beneficiary.')
            ->assertSee('Completion date will be calculated automatically.')
            ->assertSee('all initial requirements are verified before starting the enrollment.')
            ->assertSee('FR-#ACTIVE')
            ->assertSee('FR-#ATTENTION')
            ->assertSee('FR-#COMPLETE')
            ->assertDontSee('FR-#OUTSIDE')
            ->assertSee('Month 2 of 3')
            ->assertSee('Needs Attention')
            ->assertSee('LSWDO referral')
            ->assertSee('LSWDO-TEST-001')
            ->assertViewHas('summary', [
                'assigned' => 3,
                'active' => 2,
                'completed' => 1,
                'attention' => 1,
            ])
            ->assertViewHas('enrollments', fn ($enrollments) => $enrollments->total() === 3);

        CarbonImmutable::setTestNow();
    }

    public function test_search_status_and_sort_filters_are_applied_on_the_server(): void
    {
        CarbonImmutable::setTestNow('2026-08-09 08:00:00');
        $mblrc = User::factory()->role('mblrc')->create();
        $matching = $this->enrollment($mblrc, 'FR-#MATCH', '2026-04-01');
        $this->enrollment($mblrc, 'FR-#OTHER', '2026-07-01');

        $response = $this->actingAs($mblrc)->get(route('mblrc.enrollments.index', [
            'search' => 'MATCH',
            'status' => 'attention',
            'sort' => 'oldest_started',
        ]));

        $response->assertOk()
            ->assertSee('FR-#MATCH')
            ->assertDontSee('FR-#OTHER')
            ->assertSee('value="attention" selected', false)
            ->assertViewHas('enrollments', fn ($enrollments) => $enrollments->total() === 1
                && $enrollments->first()->is($matching));

        CarbonImmutable::setTestNow();
    }

    public function test_beneficiary_lookup_is_bounded_and_exposes_only_safe_preview_metadata(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Preview Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $beneficiary = FormerRebel::query()->create([
            'classified_id' => 'FR-#PREVIEW',
            'firstname' => 'Sensitive First Name',
            'lastname' => 'Sensitive Last Name',
            'municipality_id' => $municipality->id,
        ]);

        $response = $this->actingAs($mblrc)->getJson(route('mblrc.enrollments.beneficiaries', ['search' => 'PREVIEW']));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.classified_id', 'FR-#PREVIEW')
            ->assertJsonPath('0.municipality', 'Preview Municipality')
            ->assertJsonPath('0.eligible', true)
            ->assertJsonMissing(['firstname' => 'Sensitive First Name'])
            ->assertJsonMissing(['lastname' => 'Sensitive Last Name']);

        $this->assertSame($beneficiary->id, $response->json('0.id'));
    }

    public function test_duplicate_enrollment_is_rejected_by_the_backend(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();
        $beneficiary = FormerRebel::query()->create([
            'classified_id' => 'FR-#DUPLICATE',
            'firstname' => 'Duplicate',
            'lastname' => 'Enrollment',
        ]);
        $payload = [
            'former_rebel_id' => $beneficiary->id,
            'integration_started_at' => '2026-08-01',
        ];

        $this->actingAs($mblrc)->post(route('mblrc.enrollments.store'), $payload)->assertRedirect();
        $this->actingAs($mblrc)->post(route('mblrc.enrollments.store'), $payload)
            ->assertSessionHasErrors('former_rebel_id');

        $this->assertDatabaseCount('mblrc_enrollments', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $mblrc->id,
            'action' => 'integration_enrollment_started',
            'entity_type' => MblrcEnrollment::class,
        ]);
        $this->assertDatabaseHas('fr_program_statuses', [
            'former_rebel_id' => $beneficiary->id,
            'reintegration_status' => 'On-going',
            'reintegration_date' => null,
        ]);
    }

    public function test_lookup_does_not_expose_an_enrollment_assigned_to_another_mblrc_user(): void
    {
        $owner = User::factory()->role('mblrc')->create();
        $viewer = User::factory()->role('mblrc')->create();
        $enrollment = $this->enrollment($owner, 'FR-#PRIVATE-ENROLLMENT', '2026-08-01');

        $response = $this->actingAs($viewer)->getJson(route('mblrc.enrollments.beneficiaries', [
            'beneficiary_id' => $enrollment->former_rebel_id,
        ]));

        $response->assertOk()
            ->assertJsonPath('0.eligible', false)
            ->assertJsonPath('0.has_existing_enrollment', true)
            ->assertJsonPath('0.existing_enrollment', null)
            ->assertJsonMissing(['started_at' => '2026-08-01']);
    }

    public function test_expected_completion_uses_three_calendar_months_without_overflow(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();
        $enrollment = $this->enrollment($mblrc, 'FR-#MONTH-END', '2026-01-31');

        $this->assertSame('2026-04-30', $enrollment->expectedCompletionDate()?->toDateString());
        $this->assertSame(1, $enrollment->monitoringMonth(CarbonImmutable::parse('2026-02-27')));
        $this->assertSame(2, $enrollment->monitoringMonth(CarbonImmutable::parse('2026-02-28')));
        $this->assertSame(3, $enrollment->monitoringMonth(CarbonImmutable::parse('2026-03-31')));
        $this->assertTrue($enrollment->needsAttention(CarbonImmutable::parse('2026-04-30')));
    }

    public function test_assigned_mblrc_user_can_fast_forward_monitoring_in_testing_only(): void
    {
        CarbonImmutable::setTestNow('2026-08-26 08:00:00');
        $mblrc = User::factory()->role('mblrc')->create();
        $enrollment = $this->enrollment($mblrc, 'FR-#BYPASS', '2026-08-01');

        $this->actingAs($mblrc)->get(route('mblrc.enrollments.index'))
            ->assertOk()
            ->assertSee('Bypass Three-Month Period (Testing Only)');

        $this->actingAs($mblrc)
            ->post(route('mblrc.enrollments.bypass-monitoring', $enrollment))
            ->assertRedirect(route('mblrc.enrollments.index').'#enrollment-'.$enrollment->id)
            ->assertSessionHas('success');

        $this->assertSame('2026-05-26', $enrollment->fresh()->integration_started_at?->toDateString());
        $this->assertTrue($enrollment->fresh()->needsAttention());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $mblrc->id,
            'action' => 'integration_monitoring_period_bypassed_for_testing',
            'entity_type' => MblrcEnrollment::class,
            'entity_id' => $enrollment->id,
        ]);

        CarbonImmutable::setTestNow();
    }

    public function test_monitoring_bypass_is_assignment_scoped(): void
    {
        $owner = User::factory()->role('mblrc')->create();
        $otherMblrc = User::factory()->role('mblrc')->create();
        $enrollment = $this->enrollment($owner, 'FR-#BYPASS-PRIVATE', now()->toDateString());

        $this->actingAs($otherMblrc)
            ->post(route('mblrc.enrollments.bypass-monitoring', $enrollment))
            ->assertForbidden();

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'integration_monitoring_period_bypassed_for_testing',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function test_monitoring_bypass_is_hidden_and_forbidden_outside_local_or_testing(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();
        $enrollment = $this->enrollment($mblrc, 'FR-#NO-PRODUCTION-BYPASS', now()->toDateString());
        $originalEnvironment = $this->app->environment();

        $this->app->detectEnvironment(fn () => 'production');

        try {
            $this->actingAs($mblrc)->get(route('mblrc.enrollments.index'))
                ->assertOk()
                ->assertDontSee('Bypass Three-Month Period (Testing Only)');

            $csrfToken = 'production-environment-test-token';
            $this->actingAs($mblrc)
                ->withSession(['_token' => $csrfToken])
                ->post(route('mblrc.enrollments.bypass-monitoring', $enrollment), ['_token' => $csrfToken])
                ->assertForbidden();
        } finally {
            $this->app->detectEnvironment(fn () => $originalEnvironment);
        }

        $this->assertSame(now()->toDateString(), $enrollment->fresh()->integration_started_at?->toDateString());
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'integration_monitoring_period_bypassed_for_testing',
            'entity_id' => $enrollment->id,
        ]);
    }

    private function enrollment(
        User $mblrc,
        string $classifiedId,
        string $startedAt,
        string $status = 'in_progress',
        ?Municipality $municipality = null,
    ): MblrcEnrollment {
        $beneficiary = FormerRebel::query()->create([
            'classified_id' => $classifiedId,
            'firstname' => 'Synthetic',
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality?->id,
        ]);

        return MblrcEnrollment::query()->create([
            'former_rebel_id' => $beneficiary->id,
            'assigned_user_id' => $mblrc->id,
            'created_by' => $mblrc->id,
            'status' => $status,
            'integration_started_at' => $startedAt,
        ]);
    }
}

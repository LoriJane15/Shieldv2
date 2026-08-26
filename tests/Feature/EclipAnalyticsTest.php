<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipAssistanceRequest;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\EclipAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_global_aggregates_and_financial_totals(): void
    {
        $first = $this->caseIn('First Municipality', 'FR-#AN01', EclipCaseStatus::Completed, '1000.00');
        $second = $this->caseIn('Second Municipality', 'FR-#AN02', EclipCaseStatus::AssistanceAssessment, '500.00');
        $first->fundTransactions()->create($this->fundingData($first, 'allocation', '1000.00', 'AN-ALLOC'));
        $second->fundTransactions()->create($this->fundingData($second, 'allocation', '250.00', 'AN-ALLOC-2'));

        $data = app(EclipAnalyticsService::class)->dashboard(User::factory()->role('admin')->create());

        $this->assertSame(2, $data['summary']['total']);
        $this->assertSame(1, $data['summary']['completed']);
        $this->assertSame(1500.0, $data['financial']['assessed']);
        $this->assertSame(1250.0, $data['financial']['allocated']);
        $this->assertCount(2, $data['municipalities']);
    }

    public function test_municipality_user_is_scoped_and_lswdo_cannot_see_financial_totals(): void
    {
        $localCase = $this->caseIn('Local Municipality', 'FR-#LOCAL', EclipCaseStatus::Completed, '800.00');
        $this->caseIn('Outside Municipality', 'FR-#OUTSIDE', EclipCaseStatus::Completed, '900.00');
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $localCase->municipality_id]);

        $data = app(EclipAnalyticsService::class)->dashboard($lswdo);

        $this->assertSame(1, $data['summary']['total']);
        $this->assertSame(1, $data['summary']['completed']);
        $this->assertNull($data['financial']);
        $this->assertSame([], $data['municipalities']);
    }

    public function test_financial_role_only_receives_its_municipality_totals(): void
    {
        $localCase = $this->caseIn('Funding Analytics Municipality', 'FR-#FUNDLOCAL', EclipCaseStatus::FundsAllocated, '750.00');
        $outside = $this->caseIn('Outside Funding Municipality', 'FR-#FUNDOUT', EclipCaseStatus::FundsAllocated, '2500.00');
        $officer = User::factory()->role('eclip_funding_officer')->create(['municipality_id' => $localCase->municipality_id]);

        $data = app(EclipAnalyticsService::class)->dashboard($officer);

        $this->assertSame(750.0, $data['financial']['requested']);
        $this->assertSame(750.0, $data['financial']['assessed']);
        $this->assertNotSame($outside->municipality_id, $localCase->municipality_id);
    }

    public function test_dilg_fms_without_a_municipality_can_view_national_analytics(): void
    {
        $this->caseIn('FMS Analytics Municipality', 'FR-#FMS', EclipCaseStatus::FundsAllocated, '1250.00');
        $fms = User::factory()->role('dilg_fms')->create(['municipality_id' => null]);

        $response = $this->actingAs($fms)->get(route('eclip.analytics.index'));

        $response->assertOk()
            ->assertViewHas('scopeLabel', 'All municipalities')
            ->assertViewHas('financial', fn (array $financial) => $financial['assessed'] === 1250.0);
    }

    public function test_katuparan_admin_analytics_uses_the_horizontal_navigation_layout(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->get(route('eclip.analytics.index'))
            ->assertOk()
            ->assertSee('assets/css/horizontal-layout-light/style.css', false)
            ->assertDontSee('assets/css/vertical-layout-light/style.css', false);
    }

    public function test_local_committee_analytics_uses_dashboard_title_and_case_progress_icons(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Local Analytics Municipality']);
        $committee = User::factory()->role('local_eclip_committee')->create(['municipality_id' => $municipality->id]);

        $this->actingAs($committee)->get(route('eclip.analytics.index'))
            ->assertOk()
            ->assertSee('<div class="analytics-page local-committee-analytics">', false)
            ->assertSee('<div class="analytics-title-main">', false)
            ->assertSee('<span class="analytics-title-icon">', false)
            ->assertSee('mdi-clipboard-text-outline', false)
            ->assertSee('mdi-clipboard-check-outline', false)
            ->assertSee('mdi-progress-clock', false)
            ->assertSee('mdi-reply-outline', false);
    }

    public function test_aggregate_export_contains_no_beneficiary_identifiers_and_is_audited(): void
    {
        $this->caseIn('=Formula Municipality', 'FR-#PRIVATE-MARKER', EclipCaseStatus::Completed, '1000.00');
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get(route('eclip.analytics.export'))->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('SHIELD 2.0 E-CLIP Aggregate Report', $content);
        $this->assertStringContainsString("'=Formula Municipality", $content);
        $this->assertStringNotContainsString('FR-#PRIVATE-MARKER', $content);
        $this->assertStringNotContainsString('Synthetic Analytics', $content);
        $this->assertDatabaseHas('eclip_report_exports', [
            'user_id' => $admin->id,
            'format' => 'csv',
            'financial_included' => true,
        ]);
    }

    public function test_unauthorized_roles_cannot_view_or_export_eclip_analytics(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();

        $this->actingAs($mblrc)->get(route('eclip.analytics.index'))->assertForbidden();
        $this->actingAs($mblrc)->get(route('eclip.analytics.export'))->assertForbidden();
        $this->assertDatabaseCount('eclip_report_exports', 0);
    }

    public function test_average_stage_time_uses_status_history_without_exposing_cases(): void
    {
        $case = $this->caseIn('Timing Municipality', 'FR-#TIMING', EclipCaseStatus::Eligible, '100.00');
        $actor = User::factory()->role('mblrc')->create();
        $draftHistory = $case->statusHistories()->create([
            'user_id' => $actor->id, 'to_status' => EclipCaseStatus::Draft->value,
        ]);
        $draftHistory->created_at = now()->subHours(4);
        $draftHistory->saveQuietly();
        $submittedHistory = $case->statusHistories()->create([
            'user_id' => $actor->id, 'from_status' => EclipCaseStatus::Draft->value,
            'to_status' => EclipCaseStatus::SubmittedForEligibility->value,
        ]);
        $submittedHistory->created_at = now()->subHours(2);
        $submittedHistory->saveQuietly();
        $admin = User::factory()->role('admin')->create();

        $data = app(EclipAnalyticsService::class)->dashboard($admin);

        $this->assertSame(2.0, $data['stageHours'][EclipCaseStatus::Draft->label()]);
    }

    private function caseIn(string $municipalityName, string $classifiedId, EclipCaseStatus $status, string $amount): EclipCase
    {
        $municipality = Municipality::query()->create(['name' => $municipalityName]);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => $classifiedId, 'firstname' => 'Synthetic', 'lastname' => 'Analytics',
            'municipality_id' => $municipality->id,
        ]);
        $creator = User::factory()->role('mblrc')->create();
        $assessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-'.str_replace(['FR-#', '-'], '', $classifiedId),
            'former_rebel_id' => $formerRebel->id, 'municipality_id' => $municipality->id,
            'created_by' => $creator->id, 'status' => $status,
        ]);
        $category = EclipAssistanceCategory::query()->create([
            'code' => 'CAT_'.$municipality->id, 'name' => 'Synthetic Analytics Category', 'is_active' => true,
        ]);
        $request = EclipAssistanceRequest::query()->create([
            'eclip_case_id' => $case->id, 'created_by' => $assessor->id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $request->revisions()->create([
            'category_id' => $category->id, 'revision_number' => 1,
            'requested_amount' => $amount, 'assessed_amount' => $amount,
            'justification' => 'Synthetic analytics justification.', 'created_by' => $assessor->id,
        ]);

        return $case;
    }

    private function fundingData(EclipCase $case, string $type, string $amount, string $reference): array
    {
        $request = $case->assistanceRequest;

        return [
            'assistance_request_id' => $request->id,
            'assistance_revision_id' => $request->latestRevision->id,
            'type' => $type, 'amount' => $amount, 'reference_number' => $reference,
            'transaction_date' => now(), 'created_by' => User::factory()->role('eclip_funding_officer')->create(['municipality_id' => $case->municipality_id])->id,
        ];
    }
}

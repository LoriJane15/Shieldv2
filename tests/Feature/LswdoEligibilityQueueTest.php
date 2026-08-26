<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\Barangay;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LswdoEligibilityQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_counts_and_results_remain_scoped_to_the_lswdo_municipality(): void
    {
        $localMunicipality = Municipality::query()->create(['name' => 'Local Queue Municipality']);
        $outsideMunicipality = Municipality::query()->create(['name' => 'Outside Queue Municipality']);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $localMunicipality->id]);

        $submitted = $this->createCase($localMunicipality, 'ECLIP-LOCAL-001', 'FR-#L001', 'Local', 'Submitted', EclipCaseStatus::SubmittedForEligibility, now()->subDays(3), $lswdo);
        $this->createCase($localMunicipality, 'ECLIP-LOCAL-002', 'FR-#L002', 'Local', 'Eligible', EclipCaseStatus::Eligible, now()->subDays(2), $lswdo);
        $this->createCase($localMunicipality, 'ECLIP-LOCAL-003', 'FR-#L003', 'Local', 'Certified', EclipCaseStatus::DocumentsCertified, now()->subDay(), $lswdo);
        $outside = $this->createCase($outsideMunicipality, 'ECLIP-OUTSIDE-001', 'FR-#OUT1', 'Outside', 'Beneficiary', EclipCaseStatus::SubmittedForEligibility, now());

        $response = $this->actingAs($lswdo)->get(route('lswdo.eclip.index'));

        $response->assertOk()
            ->assertViewHas('summary', [
                'total' => 3,
                'awaiting' => 1,
                'eligible' => 1,
                'certified' => 1,
            ])
            ->assertViewHas('cases', function ($cases) use ($submitted, $outside) {
                return $cases->contains('id', $submitted->id) && ! $cases->contains('id', $outside->id);
            })
            ->assertSee('Local Submitted')
            ->assertDontSee('Outside Beneficiary');
    }

    public function test_queue_can_search_filter_sort_and_preserve_query_parameters(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Filtered Queue Municipality']);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);

        $oldest = $this->createCase($municipality, 'ECLIP-FILTER-OLD', 'FR-#SEARCH', 'Maria', 'Searchable', EclipCaseStatus::Eligible, now()->subMonth(), $lswdo);
        $this->createCase($municipality, 'ECLIP-FILTER-NEW', 'FR-#OTHER', 'Another', 'Person', EclipCaseStatus::SubmittedForEligibility, now(), $lswdo);

        $response = $this->actingAs($lswdo)->get(route('lswdo.eclip.index', [
            'search' => 'Maria',
            'status' => EclipCaseStatus::Eligible->value,
            'sort' => 'oldest',
        ]));

        $response->assertOk()
            ->assertViewHas('cases', function ($cases) use ($oldest) {
                return $cases->total() === 1
                    && $cases->first()->is($oldest)
                    && str_contains($cases->url(2), 'search=Maria')
                    && str_contains($cases->url(2), 'status=eligible')
                    && str_contains($cases->url(2), 'sort=oldest');
            })
            ->assertSee('View Eligibility')
            ->assertSee('Maria Searchable')
            ->assertDontSee('Another Person');
    }

    public function test_queue_supports_full_lifecycle_status_filters_for_assigned_cases(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Validation Municipality']);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $approved = $this->createCase($municipality, 'ECLIP-LIFECYCLE-001', 'FR-#LIFE', 'Lifecycle', 'Case', EclipCaseStatus::Approved, now(), $lswdo);

        $this->actingAs($lswdo)
            ->get(route('lswdo.eclip.index', ['status' => EclipCaseStatus::Approved->value]))
            ->assertOk()
            ->assertSee($approved->case_number);
    }

    private function createCase(
        Municipality $municipality,
        string $caseNumber,
        string $classifiedId,
        string $firstname,
        string $lastname,
        EclipCaseStatus $status,
        $submittedAt,
        ?User $assignee = null,
    ): EclipCase {
        $barangay = Barangay::query()->firstOrCreate([
            'municipality_id' => $municipality->id,
            'name' => 'Queue Test Barangay',
        ]);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => $classifiedId,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'municipality_id' => $municipality->id,
            'barangay_id' => $barangay->id,
        ]);

        $case = EclipCase::query()->create([
            'case_number' => $caseNumber,
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id,
            'created_by' => User::factory()->role('mblrc')->create()->id,
            'status' => $status,
            'submitted_at' => $submittedAt,
        ]);

        if ($assignee) {
            $case->participantAssignments()->create([
                'user_id' => $assignee->id,
                'participant_role' => 'case_processor',
                'assigned_by' => $case->created_by,
                'assigned_at' => now(),
                'is_active' => true,
            ]);
        }

        return $case;
    }
}

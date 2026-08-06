<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipAssistanceRequest;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EclipAssistanceReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_then_full_release_completes_case_with_immutable_history(): void
    {
        Storage::fake('local');
        [$case, $committee] = $this->transferredCase();

        $this->release($case, $committee, '400.00', 'RELEASE-001');
        $this->assertSame(EclipCaseStatus::ReleasePending, $case->fresh()->status);

        $this->release($case, $committee, '600.00', 'RELEASE-002');
        $this->assertSame(EclipCaseStatus::Completed, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_assistance_releases', 2);
        $this->assertDatabaseHas('eclip_status_histories', ['eclip_case_id' => $case->id, 'to_status' => EclipCaseStatus::AssistanceReleased->value]);
        $this->assertDatabaseHas('eclip_status_histories', ['eclip_case_id' => $case->id, 'to_status' => EclipCaseStatus::Completed->value]);
        $case->assistanceReleases->each(fn ($release) => Storage::disk('local')->assertExists($release->acknowledgment_path));

        $this->actingAs($committee)->post(route('local_eclip.releases.store', $case), [
            'amount' => '1.00', 'release_reference' => 'AFTER-COMPLETE',
            'released_at' => now()->toDateString(),
            'acknowledgment' => UploadedFile::fake()->create('ack.pdf', 10, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_release_cannot_exceed_transferred_amount(): void
    {
        Storage::fake('local');
        [$case, $committee] = $this->transferredCase();
        $this->release($case, $committee, '900.00', 'RELEASE-001');

        $this->actingAs($committee)->post(route('local_eclip.releases.store', $case), [
            'amount' => '100.01', 'release_reference' => 'RELEASE-OVER',
            'released_at' => now()->toDateString(),
            'acknowledgment' => UploadedFile::fake()->create('ack.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('eclip_assistance_releases', 1);
    }

    public function test_release_requires_supported_acknowledgment_document(): void
    {
        Storage::fake('local');
        [$case, $committee] = $this->transferredCase();

        $this->actingAs($committee)->post(route('local_eclip.releases.store', $case), [
            'amount' => '100.00', 'release_reference' => 'NO-ACK',
            'released_at' => now()->toDateString(),
        ])->assertSessionHasErrors('acknowledgment');

        $this->actingAs($committee)->post(route('local_eclip.releases.store', $case), [
            'amount' => '100.00', 'release_reference' => 'BAD-ACK',
            'released_at' => now()->toDateString(),
            'acknowledgment' => UploadedFile::fake()->create('ack.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('acknowledgment');
    }

    public function test_committee_is_restricted_to_its_municipality(): void
    {
        [$case] = $this->transferredCase();
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Release Municipality']);
        $outsideCommittee = User::factory()->role('local_eclip_committee')->create(['municipality_id' => $otherMunicipality->id]);

        $this->actingAs($outsideCommittee)->get(route('local_eclip.cases.show', $case))->assertForbidden();
    }

    public function test_acknowledgment_download_is_private_and_authorized(): void
    {
        Storage::fake('local');
        [$case, $committee] = $this->transferredCase();
        $this->release($case, $committee, '100.00', 'RELEASE-001');
        $release = $case->assistanceReleases()->firstOrFail();

        $this->actingAs($committee)->get(route('local_eclip.releases.acknowledgment', $release))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs(User::factory()->role('lgu')->create(['municipality_id' => $case->municipality_id]))
            ->get(route('local_eclip.releases.acknowledgment', $release))->assertForbidden();
    }

    private function transferredCase(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Release Municipality']);
        $formerRebel = FormerRebel::query()->create(['classified_id' => 'FR-#REL1', 'firstname' => 'Synthetic', 'lastname' => 'Release', 'municipality_id' => $municipality->id]);
        $mblrc = User::factory()->role('mblrc')->create();
        $assessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $municipality->id]);
        $reviewer = User::factory()->role('dilg_reviewer')->create(['municipality_id' => $municipality->id]);
        $funding = User::factory()->role('eclip_funding_officer')->create(['municipality_id' => $municipality->id]);
        $committee = User::factory()->role('local_eclip_committee')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create(['case_number' => 'ECLIP-REL-000001', 'former_rebel_id' => $formerRebel->id, 'municipality_id' => $municipality->id, 'created_by' => $mblrc->id, 'status' => EclipCaseStatus::FundsTransferred]);
        $category = EclipAssistanceCategory::query()->create(['code' => 'REL_TEST', 'name' => 'Release Test', 'is_active' => true]);
        $request = EclipAssistanceRequest::query()->create(['eclip_case_id' => $case->id, 'created_by' => $assessor->id, 'status' => 'approved', 'submitted_at' => now()]);
        $revision = $request->revisions()->create(['category_id' => $category->id, 'revision_number' => 1, 'requested_amount' => '1000.00', 'assessed_amount' => '1000.00', 'justification' => 'Synthetic release justification.', 'created_by' => $assessor->id]);
        $case->dilgReviews()->create(['assistance_request_id' => $request->id, 'assistance_revision_id' => $revision->id, 'reviewed_by' => $reviewer->id, 'decision' => 'approved', 'reviewed_at' => now()]);
        foreach (['allocation', 'transfer'] as $type) {
            $case->fundTransactions()->create(['assistance_request_id' => $request->id, 'assistance_revision_id' => $revision->id, 'type' => $type, 'amount' => '1000.00', 'reference_number' => strtoupper($type).'-REL', 'transaction_date' => now(), 'created_by' => $funding->id]);
        }

        return [$case, $committee];
    }

    private function release(EclipCase $case, User $committee, string $amount, string $reference): void
    {
        $this->actingAs($committee)->post(route('local_eclip.releases.store', $case), [
            'amount' => $amount, 'release_reference' => $reference,
            'released_at' => now()->toDateString(),
            'acknowledgment' => UploadedFile::fake()->create("{$reference}.pdf", 20, 'application/pdf'),
        ])->assertRedirect();
    }
}

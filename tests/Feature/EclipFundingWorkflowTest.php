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

class EclipFundingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_and_complete_allocations_advance_controlled_statuses(): void
    {
        [$case, $officer] = $this->approvedCase();

        $this->record($case, $officer, 'allocation', '400.00', 'ALLOC-001');
        $this->assertSame(EclipCaseStatus::FundAllocationPending, $case->fresh()->status);

        $this->record($case, $officer, 'allocation', '600.00', 'ALLOC-002');
        $this->assertSame(EclipCaseStatus::FundsAllocated, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_fund_transactions', 2);
        $this->assertDatabaseHas('eclip_status_histories', [
            'eclip_case_id' => $case->id,
            'to_status' => EclipCaseStatus::FundsAllocated->value,
            'user_id' => $officer->id,
        ]);
    }

    public function test_allocation_cannot_exceed_dilg_approved_amount(): void
    {
        [$case, $officer] = $this->approvedCase();
        $this->record($case, $officer, 'allocation', '900.00', 'ALLOC-001');

        $this->actingAs($officer)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'allocation', 'amount' => '100.01', 'reference_number' => 'ALLOC-OVER',
            'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('eclip_fund_transactions', 1);
    }

    public function test_transfer_requires_private_proof_and_advances_when_fully_transferred(): void
    {
        Storage::fake('local');
        [$case, $officer, $regional] = $this->approvedCase();
        $this->record($case, $officer, 'allocation', '1000.00', 'ALLOC-FULL');

        $this->actingAs($regional)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'transfer', 'amount' => '1000.00', 'reference_number' => 'TRANSFER-NO-PROOF',
            'transaction_date' => now()->toDateString(),
            'nta_received_date' => now()->toDateString(), 'recipient_office' => 'DILG P/HUC/ICC Office',
        ])->assertSessionHasErrors('proof');

        $this->actingAs($regional)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'transfer', 'amount' => '1000.00', 'reference_number' => 'TRANSFER-001',
            'transaction_date' => now()->toDateString(),
            'nta_received_date' => now()->toDateString(), 'recipient_office' => 'DILG P/HUC/ICC Office',
            'proof' => UploadedFile::fake()->create('transfer-proof.pdf', 40, 'application/pdf'),
        ])->assertRedirect();

        $transaction = $case->fundTransactions()->where('type', 'transfer')->firstOrFail();
        Storage::disk('local')->assertExists($transaction->proof_path);
        $this->assertNotNull($transaction->proof_sha256);
        $this->assertSame(EclipCaseStatus::FundsTransferred, $case->fresh()->status);
        $this->assertCount(1, User::query()->where('role', 'local_eclip_committee')->firstOrFail()->notifications()->get());

        $this->actingAs($regional)->get(route('eclip_funding.transactions.proof', $transaction))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_transfer_cannot_exceed_allocated_total(): void
    {
        Storage::fake('local');
        [$case, $officer, $regional] = $this->approvedCase();
        $this->record($case, $officer, 'allocation', '1000.00', 'ALLOC-FULL');

        $this->actingAs($regional)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'transfer', 'amount' => '1000.01', 'reference_number' => 'TRANSFER-OVER',
            'transaction_date' => now()->toDateString(),
            'nta_received_date' => now()->toDateString(), 'recipient_office' => 'DILG P/HUC/ICC Office',
            'proof' => UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'),
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('eclip_fund_transactions', 1);
    }

    public function test_funding_officer_cannot_access_another_municipality(): void
    {
        [$case] = $this->approvedCase();
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Funding Municipality']);
        $outsideOfficer = User::factory()->role('eclip_funding_officer')->create(['municipality_id' => $otherMunicipality->id]);

        $this->actingAs($outsideOfficer)->get(route('eclip_funding.cases.show', $case))->assertForbidden();
        $this->actingAs($outsideOfficer)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'allocation', 'amount' => '1.00', 'reference_number' => 'OUTSIDE',
            'transaction_date' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_unauthorized_role_cannot_download_funding_proof(): void
    {
        Storage::fake('local');
        [$case, $officer, $regional] = $this->approvedCase();
        $this->record($case, $officer, 'allocation', '1000.00', 'ALLOC-FULL');
        $this->actingAs($regional)->post(route('eclip_funding.transactions.store', $case), [
            'type' => 'transfer', 'amount' => '1000.00', 'reference_number' => 'TRANSFER-001',
            'transaction_date' => now()->toDateString(),
            'nta_received_date' => now()->toDateString(), 'recipient_office' => 'DILG P/HUC/ICC Office',
            'proof' => UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'),
        ])->assertRedirect();
        $transaction = $case->fundTransactions()->where('type', 'transfer')->firstOrFail();

        $this->actingAs(User::factory()->role('lgu')->create(['municipality_id' => $case->municipality_id]))
            ->get(route('eclip_funding.transactions.proof', $transaction))->assertForbidden();
    }

    private function approvedCase(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Funding Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#FND1', 'firstname' => 'Synthetic', 'lastname' => 'Funding',
            'municipality_id' => $municipality->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $assessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $municipality->id]);
        $reviewer = User::factory()->role('dilg_reviewer')->create(['municipality_id' => $municipality->id]);
        $officer = User::factory()->role('eclip_funding_officer')->create(['municipality_id' => $municipality->id]);
        $regional = User::factory()->role('dilg_regional')->create();
        User::factory()->role('local_eclip_committee')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-FND-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::Approved,
        ]);
        $category = EclipAssistanceCategory::query()->create(['code' => 'FUND_TEST', 'name' => 'Funding Test', 'is_active' => true]);
        $request = EclipAssistanceRequest::query()->create(['eclip_case_id' => $case->id, 'created_by' => $assessor->id, 'status' => 'approved', 'submitted_at' => now()]);
        $revision = $request->revisions()->create([
            'category_id' => $category->id, 'revision_number' => 1,
            'requested_amount' => '1200.00', 'assessed_amount' => '1000.00',
            'justification' => 'Synthetic funding justification.', 'created_by' => $assessor->id,
        ]);
        $case->dilgReviews()->create([
            'assistance_request_id' => $request->id, 'assistance_revision_id' => $revision->id,
            'reviewed_by' => $reviewer->id, 'decision' => 'approved', 'reviewed_at' => now(),
        ]);

        return [$case, $officer, $regional];
    }

    private function record(EclipCase $case, User $officer, string $type, string $amount, string $reference): void
    {
        $this->actingAs($officer)->post(route('eclip_funding.transactions.store', $case), [
            'type' => $type, 'amount' => $amount, 'reference_number' => $reference,
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();
    }
}

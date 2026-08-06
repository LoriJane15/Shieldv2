<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipBasicService;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\GovAgency;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EclipBasicServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lswdo_can_refer_a_service_without_changing_case_eligibility(): void
    {
        [$case, $lswdo, $agency] = $this->actors();

        $this->actingAs($lswdo)->post(route('lswdo.eclip.basic-services.store', $case), [
            'service_type' => 'health', 'gov_agency_id' => $agency->id, 'status' => 'referred',
            'referral_date' => today()->format('Y-m-d'), 'target_completion_date' => today()->addDays(7)->format('Y-m-d'),
            'remarks' => 'Synthetic health referral.',
        ])->assertRedirect();

        $this->assertSame(EclipCaseStatus::Eligible, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_basic_services', ['eclip_case_id' => $case->id, 'service_type' => 'health', 'status' => 'referred']);
        $this->assertDatabaseHas('eclip_basic_service_histories', ['user_id' => $lswdo->id, 'action' => 'created']);
    }

    public function test_only_assigned_agency_can_update_service_delivery(): void
    {
        [$case, $lswdo, $agency] = $this->actors();
        $service = $this->service($case, $lswdo, $agency);
        $assigned = User::factory()->govAgency($agency->id)->create();
        $otherAgency = GovAgency::query()->create(['name' => 'Other Synthetic Agency', 'acronym' => 'OTHER']);
        $outside = User::factory()->govAgency($otherAgency->id)->create();

        $this->actingAs($outside)->put(route('gov_agency.eclip.basic-services.update', $service), ['status' => 'completed', 'remarks' => 'Unauthorized.'])->assertForbidden();
        $this->actingAs($assigned)->put(route('gov_agency.eclip.basic-services.update', $service), ['status' => 'completed', 'remarks' => 'Synthetic service completed.'])->assertRedirect();

        $this->assertSame('completed', $service->fresh()->status);
        $this->assertNotNull($service->fresh()->completed_at);
        $this->assertDatabaseHas('eclip_basic_service_histories', ['basic_service_id' => $service->id, 'user_id' => $assigned->id, 'action' => 'updated']);
    }

    public function test_documents_are_private_versioned_and_assignment_scoped(): void
    {
        Storage::fake('local');
        [$case, $lswdo, $agency] = $this->actors();
        $service = $this->service($case, $lswdo, $agency);
        $assigned = User::factory()->govAgency($agency->id)->create();

        foreach (['first.pdf', 'replacement.pdf'] as $name) {
            $this->actingAs($assigned)->post(route('gov_agency.eclip.basic-services.documents.store', $service), [
                'document' => UploadedFile::fake()->create($name, 20, 'application/pdf'),
            ])->assertRedirect();
        }

        $this->assertSame([1, 2], $service->documents()->orderBy('version_number')->pluck('version_number')->all());
        $document = $service->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->storage_path);
        $this->actingAs(User::factory()->role('lgu')->create())->get(route('gov_agency.eclip.basic-services.documents.download', $document))->assertForbidden();
        $this->actingAs($assigned)->get(route('gov_agency.eclip.basic-services.documents.download', $document))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs($lswdo)->get(route('lswdo.eclip.basic-services.documents.preview', $document))
            ->assertOk()->assertSee('Secure Document Preview')
            ->assertSee(route('lswdo.eclip.basic-services.documents.download', $document), false);
    }

    public function test_lswdo_cannot_access_another_municipality_services(): void
    {
        [$case] = $this->actors();
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Basic Service Municipality']);
        $outside = User::factory()->role('lswdo')->create(['municipality_id' => $otherMunicipality->id]);

        $this->actingAs($outside)->get(route('lswdo.eclip.basic-services.index', $case))->assertForbidden();
    }

    private function actors(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Basic Service Municipality']);
        $creator = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $agency = GovAgency::query()->create(['name' => 'Synthetic Health Agency', 'acronym' => 'SHA']);
        $formerRebel = FormerRebel::query()->create(['classified_id' => 'FR-#BSC1', 'firstname' => 'Synthetic', 'lastname' => 'Service', 'municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create(['case_number' => 'ECLIP-BSC-000001', 'former_rebel_id' => $formerRebel->id, 'municipality_id' => $municipality->id, 'created_by' => $creator->id, 'status' => EclipCaseStatus::Eligible]);

        return [$case, $lswdo, $agency];
    }

    private function service(EclipCase $case, User $lswdo, GovAgency $agency): EclipBasicService
    {
        return EclipBasicService::query()->create(['eclip_case_id' => $case->id, 'service_type' => 'health', 'gov_agency_id' => $agency->id, 'status' => 'referred', 'created_by' => $lswdo->id, 'updated_by' => $lswdo->id]);
    }
}

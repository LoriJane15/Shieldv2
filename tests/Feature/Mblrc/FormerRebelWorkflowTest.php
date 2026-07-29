<?php

namespace Tests\Feature\Mblrc;

use App\Models\Barangay;
use App\Models\FormerRebel;
use App\Models\FrGovernmentAssistance;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormerRebelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $mblrc;

    private Municipality $municipality;

    private Barangay $barangay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mblrc = User::factory()->role('mblrc')->create();
        $this->municipality = Municipality::query()->create(['name' => 'Test Municipality']);
        $this->barangay = Barangay::query()->create([
            'municipality_id' => $this->municipality->id,
            'name' => 'Test Barangay',
        ]);
    }

    public function test_only_mblrc_users_can_access_former_rebel_records(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->get(route('mblrc.fr.index'))
            ->assertForbidden();
    }

    public function test_mblrc_can_register_a_normalized_former_rebel_record(): void
    {
        $this->actingAs($this->mblrc)
            ->post(route('mblrc.fr.store'), $this->validProfile([
                'firstname' => '  Juan  ',
                'contact_num' => '0917 123-4567',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('former_rebels', [
            'classified_id' => 'FR-#0001',
            'firstname' => 'Juan',
            'contact_num' => '09171234567',
            'municipality_id' => $this->municipality->id,
            'barangay_id' => $this->barangay->id,
        ]);
    }

    public function test_barangay_must_belong_to_selected_municipality(): void
    {
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Municipality']);
        $otherBarangay = Barangay::query()->create([
            'municipality_id' => $otherMunicipality->id,
            'name' => 'Other Barangay',
        ]);

        $this->actingAs($this->mblrc)
            ->post(route('mblrc.fr.store'), $this->validProfile([
                'barangay_id' => $otherBarangay->id,
            ]))
            ->assertSessionHasErrors('barangay_id');

        $this->assertDatabaseCount('former_rebels', 0);
    }

    public function test_location_update_is_recorded_and_sensitive_json_is_not_cacheable(): void
    {
        $formerRebel = $this->createFormerRebel();

        $this->actingAs($this->mblrc)
            ->postJson(route('mblrc.fr.location.save', $formerRebel), [
                'placement_address' => 'Protected Test Location',
                'latitude' => 6.75,
                'longitude' => 125.35,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('fr_location_histories', [
            'former_rebel_id' => $formerRebel->id,
            'placement_address' => 'Protected Test Location',
            'updated_by' => $this->mblrc->name,
        ]);

        $this->actingAs($this->mblrc)
            ->getJson(route('mblrc.fr.location.history', $formerRebel))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_program_status_does_not_invent_a_reintegration_date(): void
    {
        $formerRebel = $this->createFormerRebel();

        $this->actingAs($this->mblrc)
            ->putJson(route('mblrc.fr.program-status.update', $formerRebel), [
                'reintegration_status' => 'On-going',
                'reintegration_date' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('fr_program_statuses', [
            'former_rebel_id' => $formerRebel->id,
            'reintegration_status' => 'On-going',
            'reintegration_date' => null,
        ]);
    }

    public function test_readding_a_skill_updates_its_proficiency_without_duplication(): void
    {
        $formerRebel = $this->createFormerRebel();

        foreach (['Beginner', 'Advanced'] as $proficiency) {
            $this->actingAs($this->mblrc)
                ->postJson(route('mblrc.fr.skills.store', $formerRebel), [
                    'skill_name' => 'Welding',
                    'proficiency_level' => $proficiency,
                ])
                ->assertOk();
        }

        $this->assertDatabaseCount('fr_skills', 1);
        $this->assertDatabaseHas('fr_skills', [
            'former_rebel_id' => $formerRebel->id,
            'skill_name' => 'Welding',
            'proficiency_level' => 'Advanced',
        ]);
    }

    public function test_education_work_update_can_clear_the_current_occupation(): void
    {
        $formerRebel = $this->createFormerRebel(['occupation' => 'Farmer']);

        $this->actingAs($this->mblrc)
            ->postJson(route('mblrc.fr.education.update', $formerRebel), [
                'educational_attainment' => 'High School',
                'occupation' => null,
            ])
            ->assertOk();

        $this->assertNull($formerRebel->fresh()->occupation);
    }

    public function test_assistance_certificates_are_private_and_require_mblrc_access(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $formerRebel = $this->createFormerRebel();

        $response = $this->actingAs($this->mblrc)
            ->post(route('mblrc.fr.assistance.store', $formerRebel), [
                'assistance_type' => 'Synthetic livelihood assistance',
                'status' => 'Pending',
                'certificate' => UploadedFile::fake()->create('certificate.pdf', 20, 'application/pdf'),
            ])
            ->assertOk();

        $assistance = FrGovernmentAssistance::query()->firstOrFail();
        Storage::disk('local')->assertExists($assistance->certificate_file);
        Storage::disk('public')->assertMissing($assistance->certificate_file);
        $response->assertJsonPath('assistance.id', $assistance->id);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('mblrc.fr.assistance.certificate', $assistance))
            ->assertForbidden();

        $this->actingAs($this->mblrc)
            ->get(route('mblrc.fr.assistance.certificate', $assistance))
            ->assertOk();
    }

    public function test_recorded_assistance_and_former_rebel_history_cannot_be_deleted(): void
    {
        $formerRebel = $this->createFormerRebel();
        $assistance = $formerRebel->assistances()->create([
            'assistance_type' => 'Recorded assistance',
            'status' => 'Completed',
            'date_received' => '2026-01-15',
        ]);

        $this->actingAs($this->mblrc)
            ->deleteJson(route('mblrc.fr.assistance.destroy', $assistance))
            ->assertUnprocessable();

        $this->actingAs($this->mblrc)
            ->delete(route('mblrc.fr.destroy', $formerRebel))
            ->assertUnprocessable();

        $this->assertDatabaseHas('fr_government_assistances', ['id' => $assistance->id]);
        $this->assertDatabaseHas('former_rebels', ['id' => $formerRebel->id]);
    }

    public function test_unused_former_rebel_and_pending_assistance_can_be_deleted(): void
    {
        $formerRebel = $this->createFormerRebel();
        $assistance = $formerRebel->assistances()->create([
            'assistance_type' => 'Mistaken pending entry',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->mblrc)
            ->deleteJson(route('mblrc.fr.assistance.destroy', $assistance))
            ->assertOk();

        $this->actingAs($this->mblrc)
            ->delete(route('mblrc.fr.destroy', $formerRebel))
            ->assertRedirect(route('mblrc.fr.index'));

        $this->assertDatabaseMissing('former_rebels', ['id' => $formerRebel->id]);
    }

    public function test_classified_ids_are_unique(): void
    {
        $this->createFormerRebel();

        $this->expectException(QueryException::class);

        $this->createFormerRebel([
            'firstname' => 'Duplicate',
        ]);
    }

    public function test_former_rebel_delete_action_uses_the_acknowledgment_modal(): void
    {
        $formerRebel = $this->createFormerRebel();

        $this->actingAs($this->mblrc)
            ->get(route('mblrc.fr.index'))
            ->assertOk()
            ->assertSee('id="deleteConfirmationModal"', false)
            ->assertSee('data-delete-confirm', false)
            ->assertSee('data-delete-acknowledgment', false)
            ->assertSee(route('mblrc.fr.destroy', $formerRebel), false)
            ->assertDontSee("return confirm('", false);
    }

    private function createFormerRebel(array $attributes = []): FormerRebel
    {
        return FormerRebel::query()->create(array_merge([
            'classified_id' => 'FR-#0001',
            'firstname' => 'Synthetic',
            'lastname' => 'Record',
            'municipality_id' => $this->municipality->id,
            'barangay_id' => $this->barangay->id,
            'status' => 'Active',
        ], $attributes));
    }

    private function validProfile(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Juan',
            'lastname' => 'Test',
            'municipality_id' => $this->municipality->id,
            'barangay_id' => $this->barangay->id,
            'status' => 'Active',
        ], $overrides);
    }
}

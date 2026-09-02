<?php

namespace Tests\Feature;

use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaDocumentType;
use App\Enums\Ib39FrCategory;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\Ib39FeaDocument;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39FeaDraftSchema;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class Ib39FeaDraftEditorTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Ib39SurfacedFormerRebel $record;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->role('39th_ib')->create();
        $municipality = Municipality::query()->create(['name' => 'Test Municipality']);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => 'Test Barangay']);
        $this->record = app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic', 'last_name' => 'Draft Subject',
            'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $municipality->id, 'barangay_id' => $barangay->id,
            'surfaced_at' => '2026-08-30', 'possessed_firearms' => true,
        ], $this->actor);
    }

    public function test_all_four_official_editors_open_without_writing_and_prefill_only_the_new_fr_name(): void
    {
        foreach ($this->editableTypes() as $type) {
            $document = $this->document($type);
            $before = $document->updated_at;
            $response = $this->actingAs($this->actor)->get($this->editUrl($document))->assertOk();
            $response->assertSee($document->document_type->label());
            if ($type !== Ib39FeaDocumentType::Justification) {
                $response->assertDontSee('type="file"', false);
            }
            if (array_key_exists('fr_name', $this->blankDraft($type))) {
                $response->assertSee('Synthetic Draft Subject');
            }
            $this->assertSame(0, $document->fresh()->draft_revision);
            $this->assertNull($document->fresh()->draft_data);
            $this->assertTrue($before->equalTo($document->fresh()->updated_at));
        }

        $this->actingAs($this->actor)->get($this->editUrl($this->document(Ib39FeaDocumentType::Justification)))
            ->assertDontSee('This photo has its own upload and immutable version history below.', false);
        $this->assertDatabaseCount('ib39_fea_draft_histories', 0);
    }

    public function test_each_form_accepts_an_incomplete_draft_and_first_save_starts_processing(): void
    {
        foreach ($this->editableTypes() as $type) {
            $document = $this->document($type);
            $draft = $this->blankDraft($type);
            if (array_key_exists('fr_name', $draft)) {
                $draft['fr_name'] = 'Synthetic Draft Subject';
            } else {
                $draft[array_key_first($draft)] = 'Partial value';
            }
            $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])->assertRedirect();
            $document->refresh();
            $this->assertSame(Ib39FeaDocumentStatus::Processing, $document->status);
            $this->assertSame(1, $document->draft_revision);
            $this->assertSame(Ib39FeaDraftSchema::VERSION, $document->draft_schema_version);
            $this->assertSame($this->actor->id, $document->draft_saved_by);
            $this->assertNull($document->completed_at);
        }
    }

    public function test_draft_is_encrypted_and_history_contains_only_safe_metadata(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $draft = $this->blankDraft($document->document_type);
        $draft['remarks'] = 'SECRET-DRAFT-CONTENT';
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])->assertRedirect();

        $raw = DB::table('ib39_fea_documents')->where('id', $document->id)->value('draft_data');
        $this->assertStringNotContainsString('SECRET-DRAFT-CONTENT', $raw);
        $history = DB::table('ib39_fea_draft_histories')->where('fea_document_id', $document->id)->sole();
        $this->assertStringContainsString('remarks', $history->changed_fields);
        $this->assertStringNotContainsString('SECRET-DRAFT-CONTENT', $history->changed_fields);
        $audit = DB::table('audit_logs')->where('action', 'ib39_fea_draft_saved')->latest('id')->first();
        $this->assertStringNotContainsString('SECRET-DRAFT-CONTENT', (string) $audit->new_values);
    }

    public function test_identical_save_is_idempotent_and_stale_save_is_rejected(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Cvif);
        $draft = $this->blankDraft($document->document_type);
        $draft['fr_name'] = 'First saved name';
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])->assertRedirect();
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 1, 'draft' => $draft])->assertRedirect();
        $this->assertSame(1, $document->fresh()->draft_revision);
        $this->assertDatabaseCount('ib39_fea_draft_histories', 1);

        $draft['fr_name'] = 'Stale overwrite';
        $this->actingAs($this->actor)->from($this->editUrl($document))->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])
            ->assertSessionHasErrors('revision');
        $this->assertSame('First saved name', $document->fresh()->draft_data['fr_name']);
        $this->assertSame(1, $document->fresh()->draft_revision);
    }

    public function test_retired_legacy_values_are_preserved_encrypted_but_no_longer_editable(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $legacy = $this->blankDraft($document->document_type) + [
            'inspected_by' => 'LEGACY INSPECTOR',
            'noted_by' => 'LEGACY COMMANDER',
        ];
        $document->update(['draft_data' => $legacy, 'draft_schema_version' => 1, 'draft_revision' => 1]);

        $this->actingAs($this->actor)->get($this->editUrl($document))->assertOk()
            ->assertDontSee('name="draft[inspected_by]"', false)
            ->assertDontSee('name="draft[noted_by]"', false)
            ->assertDontSee('LEGACY INSPECTOR');

        $active = $this->blankDraft($document->document_type);
        $active['remarks'] = 'Updated active value';
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 1, 'draft' => $active])->assertRedirect();

        $saved = $document->fresh();
        $this->assertSame(Ib39FeaDraftSchema::VERSION, $saved->draft_schema_version);
        $this->assertSame('LEGACY INSPECTOR', $saved->draft_data['inspected_by']);
        $this->assertSame('LEGACY COMMANDER', $saved->draft_data['noted_by']);
        $this->assertSame('Updated active value', $saved->draft_data['remarks']);
        $raw = DB::table('ib39_fea_documents')->where('id', $document->id)->value('draft_data');
        $this->assertStringNotContainsString('LEGACY INSPECTOR', $raw);
    }

    public function test_corrected_fields_condition_and_justification_photo_forms_are_rendered(): void
    {
        $tir = $this->actingAs($this->actor)->get($this->editUrl($this->document(Ib39FeaDocumentType::Tir)))->assertOk();
        $tir->assertSee('value="GOOD"', false)->assertSee('value="FAIR"', false)->assertSee('value="SCRAP"', false)->assertDontSee('Not selected')
            ->assertDontSee('INSPECTED BY: (Signature over printed name)')
            ->assertDontSee('NOTED BY: (Signature over printed name)');
        $this->assertSame(0, substr_count($tir->getContent(), 'type="radio" name="draft[condition]" value=""'));

        $cvif = $this->actingAs($this->actor)->get($this->editUrl($this->document(Ib39FeaDocumentType::Cvif)))->assertOk();
        $cvif->assertDontSee('Inventory and technical conducted at')->assertDontSee('name="draft[pnp_representative]"', false);

        $ptis = $this->actingAs($this->actor)->get($this->editUrl($this->document(Ib39FeaDocumentType::Ptis)))->assertOk();
        foreach (['to', 'from', 'basis', 'received_by', 'inspected_by', 'commanding_officer', 'dilg_representative'] as $key) {
            $ptis->assertDontSee('name="draft['.$key.']"', false);
        }

        $justification = $this->actingAs($this->actor)->get($this->editUrl($this->document(Ib39FeaDocumentType::Justification)))->assertOk();
        $justification->assertSee('name="draft[prepared_by_position]"', false)
            ->assertSee('name="draft[reviewed_by_position]"', false)
            ->assertSee('data-photo-slot="justification_surrendered"', false)
            ->assertSee('data-photo-slot="justification_comparison"', false)
            ->assertSee('form="support-photo-form-3"', false)
            ->assertSee('form="support-photo-form-4"', false)
            ->assertDontSee('Supporting attachment only')
            ->assertDontSee('No Section 3 photo has been uploaded.')
            ->assertDontSee('No Section 4 photo has been uploaded.');
        $content = $justification->getContent();
        $this->assertSame(2, substr_count($content, '>Upload Photo</button>'));
        $draftFormStart = strpos($content, '<form method="POST"', strpos($content, 'id="draft-form"') - 200);
        $firstSupportingForm = strpos($content, '<form id="support-photo-form-3"');
        $secondSupportingForm = strpos($content, '<form id="support-photo-form-4"');
        $this->assertNotFalse($draftFormStart);
        $this->assertNotFalse($firstSupportingForm);
        $this->assertNotFalse($secondSupportingForm);
        $this->assertLessThan($draftFormStart, $firstSupportingForm);
        $this->assertLessThan($draftFormStart, $secondSupportingForm);
        $this->assertSame(substr_count($content, '<form'), substr_count($content, '</form>'));
    }

    public function test_schema_rejects_unknown_keys_tampered_arrays_server_owned_fields_and_excess_rows(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $draft = $this->blankDraft($document->document_type);
        $draft['unknown'] = 'tamper';
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft, 'status' => 'Completed'])
            ->assertSessionHasErrors(['request', 'draft']);
        $this->assertArrayNotHasKey('draft', session('_old_input', []));

        $draft = $this->blankDraft($document->document_type);
        $draft['parts'] = 'not-an-array';
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])->assertSessionHasErrors('draft.parts');

        $draft['parts'] = array_fill(0, Ib39FeaDraftSchema::MAX_ROWS + 1, ['part' => null, 'serviceable' => null, 'unserviceable' => null]);
        $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $draft])->assertSessionHasErrors('draft.parts');
        $this->assertNull($document->fresh()->draft_data);
    }

    public function test_guests_inactive_other_roles_photos_cross_processing_deleted_and_completed_are_denied(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $this->get($this->editUrl($document))->assertRedirect(route('login'));
        $inactive = User::factory()->role('39th_ib')->create(['is_active' => false]);
        $this->actingAs($inactive)->get($this->editUrl($document))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->role('admin')->create())->get($this->editUrl($document))->assertForbidden();

        $photo = $this->document(Ib39FeaDocumentType::FirearmPhoto);
        $this->actingAs($this->actor)->get($this->editUrl($photo))->assertForbidden();

        $other = $this->createOtherRecord()->feaProcessing;
        $this->actingAs($this->actor)->get(route('ib39.fea.documents.draft.edit', [$other, $document]))->assertForbidden();
        $this->record->delete();
        $this->actingAs($this->actor)->get($this->editUrl($document))->assertForbidden();

        $this->record->restore();
        $document->update(['status' => Ib39FeaDocumentStatus::Completed]);
        $this->actingAs($this->actor)->get($this->editUrl($document))->assertForbidden();
    }

    public function test_save_rolls_back_draft_status_actor_and_history_when_audit_write_fails(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Ptis);
        AuditLog::creating(fn () => throw new RuntimeException('Injected audit failure'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->actor)->put($this->updateUrl($document), ['revision' => 0, 'draft' => $this->blankDraft($document->document_type)]);
            $this->fail('The injected failure was not raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Injected audit failure', $exception->getMessage());
        }
        $document->refresh();
        $this->assertSame(Ib39FeaDocumentStatus::Pending, $document->status);
        $this->assertNull($document->draft_data);
        $this->assertNull($document->prepared_by);
        $this->assertDatabaseCount('ib39_fea_draft_histories', 0);
        $this->assertDatabaseCount('ib39_fea_document_histories', 0);
    }

    private function blankDraft(Ib39FeaDocumentType $type): array
    {
        return app(Ib39FeaDraftSchema::class)->initial($type, '');
    }

    private function editableTypes(): array
    {
        return [Ib39FeaDocumentType::Tir, Ib39FeaDocumentType::Cvif, Ib39FeaDocumentType::Ptis, Ib39FeaDocumentType::Justification];
    }

    private function document(Ib39FeaDocumentType $type): Ib39FeaDocument
    {
        return $this->record->feaProcessing->documents()->where('document_type', $type->value)->firstOrFail();
    }

    private function editUrl(Ib39FeaDocument $document): string
    {
        return route('ib39.fea.documents.draft.edit', [$document->processing, $document]);
    }

    private function updateUrl(Ib39FeaDocument $document): string
    {
        return route('ib39.fea.documents.draft.update', [$document->processing, $document]);
    }

    private function createOtherRecord(): Ib39SurfacedFormerRebel
    {
        return app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Other', 'last_name' => 'Record', 'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $this->record->municipality_id, 'barangay_id' => $this->record->barangay_id,
            'surfaced_at' => '2026-08-30', 'possessed_firearms' => true,
        ], $this->actor);
    }
}

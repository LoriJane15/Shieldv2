<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\RcspActivity;
use App\Models\RcspBarangay;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RcspEvidenceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_evidence_requires_authorization_and_has_safe_headers(): void
    {
        Storage::fake('local');
        $m = Municipality::create(['name' => 'DEMO Evidence Municipality']);
        $b = Barangay::create(['municipality_id' => $m->id, 'name' => 'DEMO Evidence Barangay']);
        $lgu = User::factory()->lgu($m->id)->create();
        $phase = RcspPhase::create(['number' => 0, 'name' => 'DEMO Evidence Phase', 'catalog_key' => 'evidence']);
        $activity = RcspActivity::create(['rcsp_phase_id' => $phase->id, 'description' => 'DEMO: Evidence']);
        $record = RcspBarangay::create(['barangay_id' => $b->id, 'municipality_id' => $m->id, 'catalog_key' => 'evidence']);
        $this->actingAs($lgu)->post(route('lgu.monitoring.submit', $record), ['phase_id' => $phase->id,
            'conduct' => [$activity->id => 'yes'], 'evidence' => [$activity->id => UploadedFile::fake()->image('safe.jpg')]])->assertSessionHasNoErrors();
        $form = RcspForm::firstOrFail();
        $this->assertStringStartsWith('private:rcsp/', $form->file);
        $this->actingAs($lgu)->get(route('rcsp.evidence', $form))->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-cache, no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $other = Municipality::create(['name' => 'DEMO Evidence Other']);
        $this->actingAs(User::factory()->lgu($other->id)->create())->get(route('rcsp.evidence', $form))->assertForbidden();
        $this->actingAs(User::factory()->role('afp')->create())->get(route('rcsp.evidence', $form))->assertForbidden();
    }

    public function test_missing_and_unsafe_paths_return_not_found(): void
    {
        $m = Municipality::create(['name' => 'DEMO Path Municipality']);
        $b = Barangay::create(['municipality_id' => $m->id, 'name' => 'DEMO Path Barangay']);
        $lgu = User::factory()->lgu($m->id)->create();
        $phase = RcspPhase::create(['number' => 0, 'name' => 'DEMO Path Phase']);
        $activity = RcspActivity::create(['rcsp_phase_id' => $phase->id, 'description' => 'DEMO: Path']);
        $record = RcspBarangay::create(['barangay_id' => $b->id, 'municipality_id' => $m->id]);
        $form = RcspForm::create(['lgu_user_id' => $lgu->id, 'rcsp_barangay_id' => $record->id,
            'rcsp_phase_id' => $phase->id, 'rcsp_activity_id' => $activity->id, 'conduct' => 'yes', 'file' => '../secret']);
        $this->actingAs($lgu)->get(route('rcsp.evidence', $form))->assertNotFound();
    }
}

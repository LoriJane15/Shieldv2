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
use Tests\TestCase;

class RcspValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $lgu;

    private User $admin;

    private RcspBarangay $record;

    private RcspPhase $phase;

    private RcspActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();
        $m = Municipality::create(['name' => 'DEMO Validation Municipality']);
        $b = Barangay::create(['municipality_id' => $m->id, 'name' => 'DEMO Validation Barangay']);
        $this->lgu = User::factory()->lgu($m->id)->create();
        $this->admin = User::factory()->role('admin')->create();
        $this->phase = RcspPhase::create(['number' => 0, 'name' => 'DEMO Validation Phase', 'catalog_key' => 'validation']);
        $this->activity = RcspActivity::create(['rcsp_phase_id' => $this->phase->id, 'description' => 'DEMO: Validate']);
        $this->record = RcspBarangay::create(['barangay_id' => $b->id, 'municipality_id' => $m->id, 'catalog_key' => 'validation']);
    }

    public function test_phase_tampering_invalid_conduct_and_blank_submission_are_rejected(): void
    {
        $other = RcspPhase::create(['number' => 0, 'name' => 'DEMO Other Phase', 'catalog_key' => 'other']);
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $other->id, 'conduct' => [$this->activity->id => 'yes']])->assertSessionHasErrors('phase_id');
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id, 'conduct' => [$this->activity->id => 'maybe']])->assertSessionHasErrors("conduct.{$this->activity->id}");
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id])->assertSessionHasErrors('conduct');
    }

    public function test_activity_mismatch_and_duplicate_barangay_are_rejected(): void
    {
        $otherPhase = RcspPhase::create(['number' => 1, 'name' => 'DEMO Later', 'catalog_key' => 'validation']);
        $otherActivity = RcspActivity::create(['rcsp_phase_id' => $otherPhase->id, 'description' => 'DEMO: Other']);
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id, 'conduct' => [$otherActivity->id => 'yes']])->assertSessionHasErrors('conduct');
        $this->actingAs($this->lgu)->post(route('lgu.rcsp.store'), ['barangay_id' => $this->record->barangay_id])->assertSessionHasErrors('barangay_id');
    }

    public function test_cross_barangay_review_and_missing_return_remark_are_rejected(): void
    {
        $form = RcspForm::create(['lgu_user_id' => $this->lgu->id, 'rcsp_barangay_id' => $this->record->id,
            'rcsp_phase_id' => $this->phase->id, 'rcsp_activity_id' => $this->activity->id, 'conduct' => 'yes']);
        $m = Municipality::create(['name' => 'DEMO Cross Municipality']);
        $b = Barangay::create(['municipality_id' => $m->id, 'name' => 'DEMO Cross Barangay']);
        $other = RcspBarangay::create(['barangay_id' => $b->id, 'municipality_id' => $m->id, 'catalog_key' => 'validation']);
        $this->actingAs($this->admin)->post(route('admin.rcsp.review', $other), ['phase_id' => $this->phase->id, 'statuses' => [$form->id => 'approved']])->assertSessionHasErrors('statuses');
        $this->actingAs($this->admin)->post(route('admin.rcsp.review', $this->record), ['phase_id' => $this->phase->id, 'statuses' => [$form->id => 'to be complied'], 'remarks' => [$form->id => '']])->assertSessionHasErrors("remarks.{$form->id}");
    }

    public function test_review_audit_is_saved_and_resubmission_clears_it(): void
    {
        $form = RcspForm::create(['lgu_user_id' => $this->lgu->id, 'rcsp_barangay_id' => $this->record->id,
            'rcsp_phase_id' => $this->phase->id, 'rcsp_activity_id' => $this->activity->id, 'conduct' => 'yes']);
        $this->actingAs($this->admin)->post(route('admin.rcsp.review', $this->record), ['phase_id' => $this->phase->id,
            'statuses' => [$form->id => 'to be complied'], 'remarks' => [$form->id => 'DEMO: Correct this.']])->assertSessionHasNoErrors();
        $this->assertSame($this->admin->id, $form->fresh()->reviewed_by_user_id);
        $this->assertNotNull($form->fresh()->reviewed_at);
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id,
            'conduct' => [$this->activity->id => 'yes']])->assertSessionHasNoErrors();
        $this->assertNull($form->fresh()->reviewed_by_user_id);
        $this->assertNull($form->fresh()->reviewed_at);
    }

    public function test_resubmission_omits_locked_approved_activities(): void
    {
        $second = RcspActivity::create(['rcsp_phase_id' => $this->phase->id, 'description' => 'DEMO: Mutable']);
        RcspForm::create(['lgu_user_id' => $this->lgu->id, 'rcsp_barangay_id' => $this->record->id,
            'rcsp_phase_id' => $this->phase->id, 'rcsp_activity_id' => $this->activity->id, 'conduct' => 'yes', 'status' => 'approved']);
        $returned = RcspForm::create(['lgu_user_id' => $this->lgu->id, 'rcsp_barangay_id' => $this->record->id,
            'rcsp_phase_id' => $this->phase->id, 'rcsp_activity_id' => $second->id, 'conduct' => 'no', 'status' => 'to be complied']);
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id,
            'conduct' => [$second->id => 'yes']])->assertSessionHasNoErrors();
        $this->assertSame('approved', RcspForm::where('rcsp_activity_id', $this->activity->id)->first()->status);
        $this->assertSame('submitted', $returned->fresh()->status);
    }

    public function test_completed_record_cannot_be_mutated(): void
    {
        $this->record->update(['status' => 'Completed', 'current_phase' => 5]);
        $this->actingAs($this->lgu)->post(route('lgu.monitoring.submit', $this->record), ['phase_id' => $this->phase->id,
            'conduct' => [$this->activity->id => 'yes']])->assertForbidden();
        $this->actingAs($this->admin)->post(route('admin.rcsp.review', $this->record), ['phase_id' => $this->phase->id, 'statuses' => [1 => 'approved']])->assertForbidden();
    }
}

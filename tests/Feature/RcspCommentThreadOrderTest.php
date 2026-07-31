<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\RcspActivity;
use App\Models\RcspBarangay;
use App\Models\RcspFileComment;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RcspCommentThreadOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_lgu_file_comments_show_oldest_first_and_append_new_messages(): void
    {
        [$form, $lgu] = $this->createFormWithOutOfOrderComments();

        $this->actingAs($lgu)
            ->get(route('lgu.monitoring.file', $form))
            ->assertSuccessful()
            ->assertSeeInOrder(['id="commentsList"', 'id="commentForm"'], false)
            ->assertSee('comment-composer mt-3', false)
            ->assertSee('comment-message', false)
            ->assertSee('max-width: calc(100% - 48px)', false)
            ->assertSee('d-flex align-items-start gap-2', false)
            ->assertSee('btn btn-primary flex-shrink-0', false)
            ->assertSeeInOrder(['Older reviewer message', 'Recent LGU reply'])
            ->assertSeeInOrder([
                'comment-content p-3 bg-light',
                'Older reviewer message',
                'd-flex align-items-start gap-2 justify-content-end',
                'comment-content p-3 bg-primary text-white',
                'Recent LGU reply',
            ], false)
            ->assertSee("document.getElementById('commentsList').append(card);", false)
            ->assertDontSee("document.getElementById('commentsList').prepend(card);", false);
    }

    public function test_katuparan_file_comments_show_oldest_first_and_append_new_messages(): void
    {
        [$form, , $admin] = $this->createFormWithOutOfOrderComments();

        $this->actingAs($admin)
            ->get(route('admin.rcsp.file', $form))
            ->assertSuccessful()
            ->assertSeeInOrder(['id="commentsList"', 'id="commentForm"'], false)
            ->assertSee('comment-composer mt-3', false)
            ->assertSee('comment-message', false)
            ->assertSee('max-width: calc(100% - 48px)', false)
            ->assertSee('d-flex align-items-start gap-2', false)
            ->assertSee('btn btn-primary flex-shrink-0', false)
            ->assertSeeInOrder(['Older reviewer message', 'Recent LGU reply'])
            ->assertSeeInOrder([
                'd-flex align-items-start gap-2 justify-content-end',
                'comment-content p-3 bg-primary text-white',
                'Older reviewer message',
                'comment-content p-3 bg-light',
                'Recent LGU reply',
            ], false)
            ->assertSee("document.getElementById('commentsList').append(card);", false)
            ->assertDontSee("document.getElementById('commentsList').prepend(card);", false);
    }

    /**
     * @return array{0: RcspForm, 1: User, 2: User}
     */
    private function createFormWithOutOfOrderComments(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Digos']);
        $barangay = Barangay::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Aplaya',
        ]);
        $rcspBarangay = RcspBarangay::query()->create([
            'barangay_id' => $barangay->id,
            'municipality_id' => $municipality->id,
            'status' => 'Ongoing',
            'current_phase' => 0,
        ]);
        $phase = RcspPhase::query()->create(['number' => 0, 'name' => 'Phase 0']);
        $activity = RcspActivity::query()->create([
            'rcsp_phase_id' => $phase->id,
            'description' => 'Community consultation',
        ]);
        $lgu = User::factory()->lgu($municipality->id)->create(['name' => 'LGU User']);
        $admin = User::factory()->role('admin')->create(['name' => 'Katuparan Reviewer']);
        $form = RcspForm::query()->create([
            'lgu_user_id' => $lgu->id,
            'rcsp_barangay_id' => $rcspBarangay->id,
            'rcsp_phase_id' => $phase->id,
            'rcsp_activity_id' => $activity->id,
            'conduct' => 'yes',
            'status' => 'submitted',
        ]);

        RcspFileComment::query()->forceCreate([
            'rcsp_form_id' => $form->id,
            'rcsp_phase_id' => $phase->id,
            'rcsp_activity_id' => $activity->id,
            'user_id' => $lgu->id,
            'text' => 'Recent LGU reply',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        RcspFileComment::query()->forceCreate([
            'rcsp_form_id' => $form->id,
            'rcsp_phase_id' => $phase->id,
            'rcsp_activity_id' => $activity->id,
            'user_id' => $admin->id,
            'text' => 'Older reviewer message',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        return [$form, $lgu, $admin];
    }
}

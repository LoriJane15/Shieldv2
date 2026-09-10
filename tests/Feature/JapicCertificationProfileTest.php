<?php

namespace Tests\Feature;

use App\Enums\Ib39FrCategory;
use App\Enums\JapicCertificationStatus;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\JapicCertificationProcessing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JapicCertificationProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_is_read_only_shows_cancellation_and_never_guesses_assistance(): void
    {
        $japic = User::factory()->role('japic')->create();
        $processing = $this->processing();
        DB::table('ib39_fr_cancellations')->insert(['ib39_surfaced_former_rebel_id' => $processing->ib39_surfaced_former_rebel_id,
            'previous_overall_status' => 'CDR Completed', 'reason' => encrypt('Authoritative cancellation reason'), 'cancelled_by' => $japic->id,
            'cancelled_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($japic)->get(route('japic.certifications.show', $processing))->assertOk()
            ->assertSee('Authoritative cancellation reason')->assertSee('Documents/Records')->assertSee('Assistance Records')
            ->assertDontSee('Start Certification')->assertDontSee('Upload Final')->assertDontSee('Complete Certification');
        $this->assertStringNotContainsString('private/cdr/secret.pdf', $response->getContent());
        $this->assertSame(0, DB::table('fr_government_assistances')->count());
    }

    public function test_assigned_processing_is_visible_only_to_its_assignee(): void
    {
        $assignee = User::factory()->role('japic')->create();
        $other = User::factory()->role('japic')->create();
        $processing = $this->processing();
        $processing->update(['assigned_to' => $assignee->id]);
        $this->actingAs($assignee)->get(route('japic.certifications.show', $processing))->assertOk();
        $this->actingAs($other)->get(route('japic.certifications.show', $processing))->assertForbidden();
    }

    public function test_shared_profile_contains_only_common_read_only_content_and_japic_never_loads_or_renders_cdr_history(): void
    {
        $japic = User::factory()->role('japic')->create();
        $processing = $this->processing();
        $cdrId = DB::table('ib39_cdr_processings')->where('ib39_surfaced_former_rebel_id', $processing->ib39_surfaced_former_rebel_id)->value('id');
        DB::table('ib39_cdr_status_histories')->insert([
            'cdr_processing_id' => $cdrId, 'from_status' => 'Ongoing', 'to_status' => 'Completed',
            'user_id' => $japic->id, 'event' => 'completed', 'remarks' => encrypt('JAPIC-MUST-NOT-RECEIVE-CDR-HISTORY'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::enableQueryLog();

        $response = $this->actingAs($japic)->get(route('japic.certifications.show', $processing))->assertOk()
            ->assertSee('FR Profile Information')->assertSee('Overall FR Status')->assertSee('Documents/Records')
            ->assertSee('JAPIC Certification Status and Timeline')->assertSee('View History')
            ->assertDontSee('Related Workflows')->assertDontSee('Current Final CDR')->assertDontSee('Secure preview')
            ->assertDontSee('Private certification photographs')->assertDontSee('Immutable draft revisions')
            ->assertDontSee('JAPIC-MUST-NOT-RECEIVE-CDR-HISTORY')->assertDontSee('CDR History');

        $this->assertFalse(collect(DB::getQueryLog())->contains(
            fn (array $query): bool => str_contains(strtolower($query['query']), 'ib39_cdr_status_histories')
        ));
        $component = file_get_contents(resource_path('views/components/surfaced-fr-profile.blade.php'));
        foreach (['<form', 'statusHistories', 'draftHistories', 'japic.certifications', 'ib39.cdr'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $component);
        }
        $this->assertStringContainsString('statusHistories', file_get_contents(app_path('Http/Controllers/Ib39/CdrController.php')));
        $this->assertStringNotContainsString('statusHistories', $response->getContent());
    }

    private function processing(): JapicCertificationProcessing
    {
        $actor = User::factory()->role('39th_ib')->create();
        $municipality = DB::table('municipalities')->insertGetId(['name' => 'Profile Municipality', 'created_at' => now(), 'updated_at' => now()]);
        $fr = DB::table('ib39_surfaced_former_rebels')->insertGetId(['reference_number' => 'JAP-PROFILE', 'first_name' => 'Profile', 'last_name' => 'Person', 'category' => Ib39FrCategory::RegularMember->value, 'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE, 'municipality_id' => $municipality, 'surfaced_at' => '2026-09-01', 'possessed_firearms' => 0, 'created_by' => $actor->id, 'created_at' => now(), 'updated_at' => now()]);
        $cdr = DB::table('ib39_cdr_processings')->insertGetId(['ib39_surfaced_former_rebel_id' => $fr, 'status' => 'Completed', 'completed_at' => now(), 'completed_by' => $actor->id, 'created_at' => now(), 'updated_at' => now()]);
        $version = DB::table('ib39_cdr_document_versions')->insertGetId(['cdr_processing_id' => $cdr, 'version_number' => 1, 'source_type' => 'uploaded', 'storage_path' => 'private/cdr/secret.pdf', 'original_filename' => 'final.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 1, 'sha256' => str_repeat('c', 64), 'created_by' => $actor->id, 'finalized_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('ib39_cdr_processings')->where('id', $cdr)->update(['current_final_version_id' => $version]);

        return JapicCertificationProcessing::query()->forceCreate(['ib39_surfaced_former_rebel_id' => $fr, 'triggering_cdr_document_version_id' => $version, 'status' => JapicCertificationStatus::Pending, 'received_at' => now(), 'due_at' => now()->addDays(14), 'lock_version' => 0]);
    }
}

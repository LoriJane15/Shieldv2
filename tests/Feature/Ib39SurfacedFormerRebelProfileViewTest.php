<?php

namespace Tests\Feature;

use App\Enums\Ib39FrCategory;
use App\Models\Barangay;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Ib39SurfacedFormerRebelProfileViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_active_39th_ib_user_can_view_a_profile(): void
    {
        $record = $this->record();

        $this->get(route('ib39.fr-profiles.show', $record))->assertRedirect(route('login'));

        $authorized = User::factory()->role('39th_ib')->create();
        $this->assertTrue($authorized->can('view', $record));
        $this->actingAs($authorized)->get(route('ib39.fr-profiles.show', $record))->assertOk();

        $inactive = User::factory()->role('39th_ib')->create(['is_active' => false]);
        $this->assertFalse($inactive->can('view', $record));
        $this->actingAs($inactive)
            ->get(route('ib39.fr-profiles.show', $record))
            ->assertRedirect(route('login'));

        $roles = [
            'super_admin', 'admin', 'lgu', 'gov_agency', 'mblrc', 'lswdo', 'japic',
            'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms',
            'local_eclip_committee', 'pnp', 'afp', 'eclip_assessor', 'dilg_reviewer',
            'eclip_funding_officer',
        ];

        foreach ($roles as $role) {
            $user = User::factory()->role($role)->create();
            $this->assertFalse($user->can('view', $record));
            $this->actingAs($user)->get(route('ib39.fr-profiles.show', $record))->assertForbidden();
        }
    }

    public function test_profile_displays_only_the_standalone_record_and_honest_workflow_fallbacks(): void
    {
        $creator = User::factory()->role('39th_ib')->create([
            'id' => 876543210,
            'name' => 'Safe Recorder Name',
            'email' => 'creator-secret@example.test',
        ]);
        $record = $this->record([
            'reference_number' => 'FR-VIEW-001',
            'first_name' => 'Approved',
            'last_name' => 'Profile',
            'category' => Ib39FrCategory::Other->value,
            'other_category_specification' => 'Approved category detail',
            'specific_location' => 'Approved location detail',
            'surfaced_at' => '2026-08-30',
            'possessed_firearms' => true,
            'initial_remarks' => 'Approved initial remarks',
            'created_by' => $creator->id,
            'created_at' => '2026-08-30 09:15:00',
        ]);
        $user = User::factory()->role('39th_ib')->create();

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', $record))
            ->assertOk()
            ->assertSee('Read-only surfaced former rebel profile')
            ->assertSee('FR-VIEW-001')
            ->assertSee('Approved Profile')
            ->assertSee('Approved category detail')
            ->assertSee('Approved location detail')
            ->assertSee('August 30, 2026')
            ->assertSee('Approved initial remarks')
            ->assertSee('Newly Recorded')
            ->assertSee('August 30, 2026 · 09:15 AM')
            ->assertSee('Recorded by')
            ->assertSee('Safe Recorder Name')
            ->assertSee('This standalone record has no secure cross-agency workflow links.')
            ->assertSee('Not securely linked — unavailable')
            ->assertSeeInOrder(['CDR processing', 'JAPIC processing', 'PSWDO processing', 'Assistance records'])
            ->assertSeeInOrder(['FEA', 'Process Status', 'Not Available', 'Documents', 'Not Available'])
            ->assertDontSee('href="/documents', false)
            ->assertDontSee('href="/eclip', false)
            ->assertDontSee('href="/assistance', false)
            ->assertDontSee('creator-secret@example.test')
            ->assertDontSee('876543210')
            ->assertDontSee('Forwarded')
            ->assertDontSee('Forwarding organization')
            ->assertDontSee('Edit Profile')
            ->assertDontSee('Delete Profile')
            ->assertDontSee('Archive Profile')
            ->assertDontSee('method="PUT"', false)
            ->assertDontSee('method="DELETE"', false);
    }

    public function test_non_firearms_profile_marks_fea_process_and_documents_not_applicable(): void
    {
        $record = $this->record(['possessed_firearms' => false]);
        $user = User::factory()->role('39th_ib')->create();

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', $record))
            ->assertOk()
            ->assertSeeInOrder(['FEA', 'Process Status', 'Not Applicable', 'Documents', 'Not Applicable'])
            ->assertDontSee('Not Available');
    }

    public function test_blank_creator_name_uses_safe_unknown_user_fallback(): void
    {
        $creator = User::factory()->role('39th_ib')->create(['name' => '   ']);
        $record = $this->record(['created_by' => $creator->id]);
        $user = User::factory()->role('39th_ib')->create();

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', $record))
            ->assertOk()
            ->assertSee('Unknown user');
    }

    public function test_viewing_profile_performs_no_writes_or_external_workflow_queries(): void
    {
        $record = $this->record();
        $user = User::factory()->role('39th_ib')->create();
        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', $record))
            ->assertOk();

        $this->assertEmpty(array_filter(
            $queries,
            fn (string $sql): bool => (bool) preg_match('/^\s*(insert|update|delete|replace|alter|create|drop|truncate)\b/', $sql),
        ));

        foreach (['former_rebels', 'fr_government_assistances', 'eclip_', 'document_access_logs'] as $externalTable) {
            $this->assertFalse(
                collect($queries)->contains(
                    fn (string $sql): bool => (bool) preg_match(
                        '/\b(from|join|into|update)\s+["`]?'.preg_quote($externalTable, '/').'/',
                        $sql,
                    ),
                ),
                "The profile query unexpectedly accessed {$externalTable}.",
            );
        }
    }

    public function test_soft_deleted_profile_is_not_available(): void
    {
        $record = $this->record();
        $record->delete();

        $user = User::factory()->role('39th_ib')->create();

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', $record->getKey()))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('ib39.fr-profiles.show', 999999999))
            ->assertNotFound();
    }

    private function record(array $overrides = []): Ib39SurfacedFormerRebel
    {
        $municipality = Municipality::query()->create(['name' => 'Profile Municipality']);
        $barangay = Barangay::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Profile Barangay',
        ]);
        $creator = User::factory()->role('39th_ib')->create();

        return Ib39SurfacedFormerRebel::query()->forceCreate(array_merge([
            'reference_number' => 'FR-PROFILE-001',
            'first_name' => 'Profile',
            'last_name' => 'Record',
            'category' => Ib39FrCategory::RegularMember->value,
            'other_category_specification' => null,
            'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE,
            'municipality_id' => $municipality->id,
            'barangay_id' => $barangay->id,
            'specific_location' => null,
            'surfaced_at' => '2026-08-31',
            'possessed_firearms' => false,
            'initial_remarks' => null,
            'created_by' => $creator->id,
        ], $overrides));
    }
}

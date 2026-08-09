<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\AgencyImplanResponse;
use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\User;
use App\Services\AgencyLogoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgencyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_manage_agencies(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->post(route('super_admin.agencies.store'), [
                'name' => 'Unauthorized Agency',
                'acronym' => 'UA',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('gov_agencies', ['acronym' => 'UA']);
    }

    public function test_super_admin_can_create_and_update_agency_with_logo(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($superAdmin)->post(route('super_admin.agencies.store'), [
            'name' => 'Agency One',
            'acronym' => 'A1',
            'profile' => $this->png('agency-one.png'),
        ])->assertSessionHasNoErrors();

        $agency = GovAgency::query()->where('acronym', 'A1')->firstOrFail();
        $oldLogo = $agency->profile;
        $this->assertSame('agency-one.png', $agency->logo_original_name);
        $this->assertSame('image/png', $agency->logo_mime_type);
        $this->assertGreaterThan(0, $agency->logo_size_bytes);
        $this->assertStringStartsWith('agency-logos/', $oldLogo);
        Storage::disk('public')->assertExists($oldLogo);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'agency_logo_uploaded',
            'entity_type' => GovAgency::class,
            'entity_id' => $agency->id,
        ]);

        $this->actingAs($superAdmin)->put(route('super_admin.agencies.update', $agency), [
            'name' => 'Agency One Updated',
            'acronym' => 'A1U',
            'profile' => $this->png('agency-one-updated.png'),
        ])->assertSessionHasNoErrors();

        $agency->refresh();
        $this->assertSame('Agency One Updated', $agency->name);
        $this->assertSame('agency-one-updated.png', $agency->logo_original_name);
        $this->assertNotSame($oldLogo, $agency->profile);
        Storage::disk('public')->assertExists($agency->profile);
        Storage::disk('public')->assertMissing($oldLogo);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'agency_logo_replaced',
            'entity_type' => GovAgency::class,
            'entity_id' => $agency->id,
        ]);
    }

    public function test_invalid_replacement_preserves_existing_logo_and_metadata(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->role('super_admin')->create();
        $agency = GovAgency::query()->create([
            'name' => 'Protected Logo Agency',
            'acronym' => 'PLA',
            'profile' => 'agency-logos/existing.png',
            'logo_original_name' => 'existing.png',
            'logo_mime_type' => 'image/png',
            'logo_size_bytes' => 123,
        ]);
        Storage::disk('public')->put($agency->profile, 'existing-logo');

        $this->actingAs($superAdmin)->put(route('super_admin.agencies.update', $agency), [
            'name' => 'Protected Logo Agency Changed',
            'acronym' => 'PLA',
            'profile' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('profile');

        $agency->refresh();
        $this->assertSame('Protected Logo Agency', $agency->name);
        $this->assertSame('agency-logos/existing.png', $agency->profile);
        $this->assertSame('existing.png', $agency->logo_original_name);
        $this->assertSame('image/png', $agency->logo_mime_type);
        $this->assertSame(123, $agency->logo_size_bytes);
        Storage::disk('public')->assertExists('agency-logos/existing.png');
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'agency_logo_replaced',
            'entity_id' => $agency->id,
        ]);
    }

    public function test_unsafe_legacy_logo_path_uses_fallback_and_is_never_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('outside-logo.png', 'must-remain');
        $agency = GovAgency::query()->create([
            'name' => 'Legacy Path Agency',
            'acronym' => 'LPA',
            'profile' => 'C:\\Users\\Admin\\agency.png',
        ]);

        $this->assertSame(asset('assets/img/kc-logo.svg'), $agency->profile_url);

        app(AgencyLogoService::class)->delete('../outside-logo.png');
        app(AgencyLogoService::class)->delete('C:\\Users\\Admin\\agency.png');
        Storage::disk('public')->assertExists('outside-logo.png');
    }

    public function test_safe_legacy_logo_filename_uses_the_existing_legacy_asset_directory(): void
    {
        $agency = GovAgency::query()->create([
            'name' => 'Safe Legacy Agency',
            'acronym' => 'SLA',
            'profile' => 'DILG.png',
        ]);

        $this->assertSame(asset('assets/uploadLogo/DILG.png'), $agency->profile_url);
    }

    public function test_agency_acronym_must_be_unique(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        GovAgency::query()->create(['name' => 'Existing Agency', 'acronym' => 'DUP']);

        $this->actingAs($superAdmin)->post(route('super_admin.agencies.store'), [
            'name' => 'Duplicate Agency',
            'acronym' => 'DUP',
        ])->assertSessionHasErrors('acronym');
    }

    public function test_agency_with_assigned_user_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $agency = GovAgency::query()->create(['name' => 'Assigned Agency', 'acronym' => 'AA']);
        User::factory()->govAgency($agency->id)->create();

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.agencies.destroy', $agency))
            ->assertUnprocessable();

        $this->assertDatabaseHas('gov_agencies', ['id' => $agency->id]);
    }

    public function test_agency_with_workflow_history_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $lgu = User::factory()->role('lgu')->create();
        $agency = GovAgency::query()->create(['name' => 'Historical Agency', 'acronym' => 'HA']);
        $implementation = Implementation::query()->create([
            'lgu_user_id' => $lgu->id,
            'issues' => 'Historical test record',
        ]);
        $response = AgencyImplanResponse::query()->create([
            'gov_agency_id' => $agency->id,
            'implementation_id' => $implementation->id,
            'response_status' => 'accepted',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.agencies.destroy', $agency))
            ->assertUnprocessable();

        $this->assertDatabaseHas('gov_agencies', ['id' => $agency->id]);
        $this->assertDatabaseHas('agency_implan_responses', ['id' => $response->id]);
    }

    public function test_unused_agency_can_be_deleted_with_its_managed_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('agency-logos/unused.png', 'unused-logo');

        $superAdmin = User::factory()->role('super_admin')->create();
        $agency = GovAgency::query()->create([
            'name' => 'Unused Agency',
            'acronym' => 'UNUSED',
            'profile' => 'agency-logos/unused.png',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.agencies.destroy', $agency))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('gov_agencies', ['id' => $agency->id]);
        Storage::disk('public')->assertMissing('agency-logos/unused.png');
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'agency_deleted',
            'entity_type' => GovAgency::class,
            'entity_id' => $agency->id,
        ]);
    }

    public function test_agency_delete_action_uses_confirmation_modal(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $agency = GovAgency::query()->create([
            'name' => 'Deletable Agency',
            'acronym' => 'DELETE',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.agencies.index'))
            ->assertOk()
            ->assertSee('id="deleteConfirmationModal"', false)
            ->assertSee('data-delete-confirm', false)
            ->assertSee(route('super_admin.agencies.destroy', $agency), false)
            ->assertDontSee("return confirm('", false);
    }

    private function png(string $name): UploadedFile
    {
        $onePixelPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $onePixelPng);
    }
}

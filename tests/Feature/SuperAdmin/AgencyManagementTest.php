<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\AgencyImplanResponse;
use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\User;
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
        Storage::disk('public')->assertExists($oldLogo);

        $this->actingAs($superAdmin)->put(route('super_admin.agencies.update', $agency), [
            'name' => 'Agency One Updated',
            'acronym' => 'A1U',
            'profile' => $this->png('agency-one-updated.png'),
        ])->assertSessionHasNoErrors();

        $agency->refresh();
        $this->assertSame('Agency One Updated', $agency->name);
        $this->assertNotSame($oldLogo, $agency->profile);
        Storage::disk('public')->assertExists($agency->profile);
        Storage::disk('public')->assertMissing($oldLogo);
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

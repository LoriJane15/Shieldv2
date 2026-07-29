<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserLogoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_user_with_cropped_logo(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->role('super_admin')->create();

        $response = $this->actingAs($superAdmin)->post(route('super_admin.users.store'), [
            'name' => 'Logo Test User',
            'username' => 'logo_test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'admin',
            'logo' => $this->png('cropped-logo.png'),
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::query()->where('username', 'logo_test')->firstOrFail();

        $this->assertStringStartsWith('logos/', $user->logo);
        Storage::disk('public')->assertExists($user->logo);
    }

    public function test_updating_without_logo_preserves_existing_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('logos/existing.png', 'existing-logo');

        $superAdmin = User::factory()->role('super_admin')->create();
        $user = User::factory()->role('admin')->create(['logo' => 'logos/existing.png']);

        $this->actingAs($superAdmin)->put(route('super_admin.users.update', $user), [
            'name' => 'Updated Name',
            'username' => $user->username,
            'role' => 'admin',
        ])->assertSessionHasNoErrors();

        $this->assertSame('logos/existing.png', $user->fresh()->logo);
        Storage::disk('public')->assertExists('logos/existing.png');
    }

    public function test_replacing_logo_stores_new_file_and_deletes_managed_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('logos/existing.png', 'existing-logo');

        $superAdmin = User::factory()->role('super_admin')->create();
        $user = User::factory()->role('admin')->create(['logo' => 'logos/existing.png']);

        $this->actingAs($superAdmin)->put(route('super_admin.users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => 'admin',
            'logo' => $this->png('replacement.png'),
        ])->assertSessionHasNoErrors();

        $newLogo = $user->fresh()->logo;

        $this->assertNotSame('logos/existing.png', $newLogo);
        Storage::disk('public')->assertExists($newLogo);
        Storage::disk('public')->assertMissing('logos/existing.png');
    }

    public function test_non_super_admin_cannot_replace_another_users_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->role('admin')->create();
        $user = User::factory()->role('lgu')->create();

        $this->actingAs($admin)->put(route('super_admin.users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => 'lgu',
            'logo' => $this->png('unauthorized.png'),
        ])->assertForbidden();

        $this->assertNull($user->fresh()->logo);
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

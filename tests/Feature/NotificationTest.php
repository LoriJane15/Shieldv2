<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_status_returns_only_the_authenticated_users_state(): void
    {
        $user = User::factory()->role('lswdo')->create();
        $otherUser = User::factory()->role('lswdo')->create();
        $latestId = $this->createNotification($user);
        $this->createNotification($otherUser);

        $this->actingAs($user)->getJson(route('notifications.status'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJson([
                'unread_count' => 1,
                'unread_ids' => [$latestId],
                'latest_id' => $latestId,
            ]);
    }

    public function test_user_can_mark_all_of_their_notifications_as_read_without_affecting_others(): void
    {
        $user = User::factory()->role('lswdo')->create();
        $otherUser = User::factory()->role('lswdo')->create();
        $this->createNotification($user);
        $this->createNotification($user);
        $this->createNotification($otherUser);

        $this->actingAs($user)->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $otherUser->fresh()->unreadNotifications()->count());
    }

    private function createNotification(User $user): string
    {
        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'Tests\\Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['case_number' => 'ECLIP-TEST', 'message' => 'Test notification.']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}

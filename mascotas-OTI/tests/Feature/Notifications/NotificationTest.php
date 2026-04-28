<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\Notification;

class NotificationTest extends TestCase
{
    public function test_user_can_list_own_notifications(): void
    {
        $user = $this->actingAsRole('CITIZEN');

        Notification::factory()->count(3)->create(['user_id' => $user->id]);
        Notification::factory()->count(2)->create(); // de otro user

        $response = $this->getJson("{$this->apiBase}/notifications")
                         ->assertStatus(200);

        // Solo debe ver las suyas
        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_get_unread_count(): void
    {
        $user = $this->actingAsRole('CITIZEN');

        Notification::factory()->count(4)->create(['user_id' => $user->id, 'is_read' => false]);
        Notification::factory()->count(2)->create(['user_id' => $user->id, 'is_read' => true]);

        $this->getJson("{$this->apiBase}/notifications/unread-count")
             ->assertStatus(200)
             ->assertJsonPath('unread_count', 4);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user         = $this->actingAsRole('CITIZEN');
        $notification = Notification::factory()->create(['user_id' => $user->id, 'is_read' => false]);

        $this->patchJson("{$this->apiBase}/notifications/{$notification->id}/read")
             ->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'id'      => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->actingAsRole('CITIZEN');
        Notification::factory()->count(5)->create(['user_id' => $user->id, 'is_read' => false]);

        $this->patchJson("{$this->apiBase}/notifications/mark-all-read")
             ->assertStatus(200);

        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $this->assertEquals(0, $unread);
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        $other        = $this->createUser('CITIZEN');
        $notification = Notification::factory()->create(['user_id' => $other->id]);

        $this->actingAsRole('CITIZEN');

        $this->patchJson("{$this->apiBase}/notifications/{$notification->id}/read")
             ->assertStatus(403);
    }

    public function test_user_can_delete_own_notification(): void
    {
        $user         = $this->actingAsRole('CITIZEN');
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->deleteJson("{$this->apiBase}/notifications/{$notification->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
<?php

namespace Tests\Feature\Posts;

use Tests\TestCase;
use App\Models\Post;
use App\Models\PostType;

class PostTest extends TestCase
{
    private PostType $postType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postType = PostType::factory()->create();
    }

    // ════════════════════════════════════════════════════════════════════
    // INDEX (público)
    // ════════════════════════════════════════════════════════════════════

    public function test_anyone_can_list_published_posts(): void
    {
        Post::factory()->count(3)->create([
            'post_type_id' => $this->postType->id,
            'status'       => 'PUBLISHED',
        ]);

        $this->getJson("{$this->apiBase}/posts")
             ->assertStatus(200)
             ->assertJsonStructure(['data']);
    }

    // ════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════

    public function test_citizen_can_create_post_as_draft(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/posts", [
            'post_type_id' => $this->postType->id,
            'title'        => 'Se perdió mi perro Firulais',
            'content'      => 'Visto por última vez en San Jerónimo...',
        ])->assertStatus(201)
          ->assertJsonPath('status', 'DRAFT');
    }

    public function test_lost_notice_changes_animal_status_to_lost(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->postJson("{$this->apiBase}/posts", [
            'post_type_id'   => $this->postType->id,
            'title'          => 'Perdí a mi perro',
            'is_lost_notice' => true,
            'animal_id'      => $animal->id,
            'lost_at'        => now()->toDateString(),
            'lost_location'  => 'Av. Cusco 100',
        ])->assertStatus(201);

        $this->assertDatabaseHas('animals', [
            'id'     => $animal->id,
            'status' => 'LOST',
        ]);

        $this->assertDatabaseHas('lost_notices', [
            'animal_id' => $animal->id,
            'status'    => 'ACTIVE',
        ]);
    }

    public function test_lost_notice_requires_animal_id_and_lost_at(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/posts", [
            'post_type_id'   => $this->postType->id,
            'title'          => 'Animal perdido',
            'is_lost_notice' => true,
            // Sin animal_id ni lost_at
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['animal_id', 'lost_at']);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_publish_post(): void
    {
        $this->actingAsRole('COORDINATOR');
        $post = Post::factory()->create([
            'post_type_id' => $this->postType->id,
            'status'       => 'DRAFT',
        ]);

        $this->patchJson("{$this->apiBase}/posts/{$post->id}/status", [
            'status' => 'PUBLISHED',
        ])->assertStatus(200)
          ->assertJsonPath('status', 'PUBLISHED');
    }

    public function test_citizen_cannot_publish_post(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');
        $post    = Post::factory()->create([
            'post_type_id' => $this->postType->id,
            'author_id'    => $citizen->id,
            'status'       => 'DRAFT',
        ]);

        $this->patchJson("{$this->apiBase}/posts/{$post->id}/status", [
            'status' => 'PUBLISHED',
        ])->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // RESOLVE LOST NOTICE
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_resolve_own_lost_notice(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner, ['status' => 'LOST']);
        $post   = Post::factory()->create([
            'post_type_id' => $this->postType->id,
            'author_id'    => $owner->id,
        ]);
        $post->lostNotice()->create([
            'animal_id'    => $animal->id,
            'lost_at'      => now()->subDays(3)->toDateString(),
            'lost_location'=> 'Algún lugar',
            'status'       => 'ACTIVE',
        ]);

        $this->patchJson("{$this->apiBase}/posts/{$post->id}/lost-notice/resolve", [
            'status' => 'FOUND',
        ])->assertStatus(200);

        // El animal regresa a ACTIVE
        $this->assertDatabaseHas('animals', [
            'id'     => $animal->id,
            'status' => 'ACTIVE',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════════════════

    public function test_author_can_delete_own_post(): void
    {
        $author = $this->actingAsRole('CITIZEN');
        $post   = Post::factory()->create([
            'post_type_id' => $this->postType->id,
            'author_id'    => $author->id,
        ]);

        $this->deleteJson("{$this->apiBase}/posts/{$post->id}")
             ->assertStatus(200);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_citizen_cannot_delete_another_authors_post(): void
    {
        $author = $this->createUser('CITIZEN');
        $post   = Post::factory()->create([
            'post_type_id' => $this->postType->id,
            'author_id'    => $author->id,
        ]);

        $this->actingAsRole('CITIZEN');

        $this->deleteJson("{$this->apiBase}/posts/{$post->id}")
             ->assertStatus(403);
    }
}
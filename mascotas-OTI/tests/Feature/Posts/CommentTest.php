<?php

namespace Tests\Feature\Posts;

use Tests\TestCase;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Comment;

class CommentTest extends TestCase
{
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $type       = PostType::factory()->create();
        $this->post = Post::factory()->create([
            'post_type_id' => $type->id,
            'status'       => 'PUBLISHED',
        ]);
    }

    public function test_citizen_can_comment_on_published_post(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/posts/{$this->post->id}/comments", [
            'content' => 'Espero que encuentren al perrito.',
        ])->assertStatus(201)
          ->assertJsonPath('status', 'VISIBLE');
    }

    public function test_cannot_comment_on_draft_post(): void
    {
        $type    = PostType::factory()->create();
        $draft   = Post::factory()->create(['post_type_id' => $type->id, 'status' => 'DRAFT']);

        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/posts/{$draft->id}/comments", [
            'content' => 'Comentario en borrador',
        ])->assertStatus(422);
    }

    public function test_author_can_edit_own_comment(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $citizen->id,
            'status'  => 'VISIBLE',
        ]);

        $this->putJson("{$this->apiBase}/posts/{$this->post->id}/comments/{$comment->id}", [
            'content' => 'Editado',
        ])->assertStatus(200)
          ->assertJsonPath('content', 'Editado');
    }

    public function test_citizen_cannot_edit_another_users_comment(): void
    {
        $other   = $this->createUser('CITIZEN');
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $other->id,
        ]);

        $this->actingAsRole('CITIZEN');

        $this->putJson("{$this->apiBase}/posts/{$this->post->id}/comments/{$comment->id}", [
            'content' => 'Hack',
        ])->assertStatus(403);
    }

    public function test_coordinator_can_moderate_comment(): void
    {
        $this->actingAsRole('COORDINATOR');
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'status'  => 'VISIBLE',
        ]);

        $this->patchJson(
            "{$this->apiBase}/posts/{$this->post->id}/comments/{$comment->id}/moderate",
            ['status' => 'HIDDEN', 'moderation_reason' => 'Lenguaje inapropiado']
        )->assertStatus(200)
         ->assertJsonPath('status', 'HIDDEN');
    }

    public function test_citizen_cannot_moderate_comment(): void
    {
        $this->actingAsRole('CITIZEN');
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $this->patchJson(
            "{$this->apiBase}/posts/{$this->post->id}/comments/{$comment->id}/moderate",
            ['status' => 'HIDDEN']
        )->assertStatus(403);
    }
}
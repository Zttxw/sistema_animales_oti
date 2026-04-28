<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'post_type_id' => PostType::factory(),
            'title'        => fake()->sentence(),
            'content'      => fake()->paragraphs(3, true),
            'author_id'    => User::factory(),
            'status'       => 'DRAFT',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'PUBLISHED']);
    }
}

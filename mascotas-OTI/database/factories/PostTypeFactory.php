<?php

namespace Database\Factories;

use App\Models\PostType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostTypeFactory extends Factory
{
    protected $model = PostType::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->randomElement(['Noticia', 'Aviso de Pérdida', 'Evento', 'Campaña', 'Alerta']),
            'description' => fake()->sentence(),
            'active'      => true,
        ];
    }
}

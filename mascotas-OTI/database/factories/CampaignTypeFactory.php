<?php

namespace Database\Factories;

use App\Models\CampaignType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignTypeFactory extends Factory
{
    protected $model = CampaignType::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->randomElement(['Vacunación', 'Esterilización', 'Desparasitación', 'Adopción', 'Censo']),
            'description' => fake()->sentence(),
            'icon'        => null,
            'active'      => true,
        ];
    }
}

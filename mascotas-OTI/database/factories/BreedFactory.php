<?php

namespace Database\Factories;

use App\Models\Breed;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreedFactory extends Factory
{
    protected $model = Breed::class;

    public function definition(): array
    {
        return [
            'species_id' => Species::factory(),
            'name'       => fake()->unique()->word() . ' ' . fake()->word(),
            'active'     => true,
        ];
    }
}

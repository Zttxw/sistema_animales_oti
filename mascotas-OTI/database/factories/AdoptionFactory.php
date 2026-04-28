<?php

namespace Database\Factories;

use App\Models\Adoption;
use App\Models\Animal;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdoptionFactory extends Factory
{
    protected $model = Adoption::class;

    public function definition(): array
    {
        return [
            'animal_id'   => Animal::factory(),
            'status'      => 'AVAILABLE',
            'reason'      => fake()->sentence(),
            'description' => fake()->paragraph(),
            'requirements' => fake()->sentence(),
            'contact'     => fake()->phoneNumber(),
        ];
    }

    public function adopted(): static
    {
        return $this->state(fn () => [
            'status'     => 'ADOPTED',
            'adopted_at' => fake()->date(),
        ]);
    }
}

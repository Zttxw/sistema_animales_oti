<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    public function definition(): array
    {
        $year = date('Y');
        $seq  = fake()->unique()->numberBetween(1, 999999);

        return [
            'municipal_code'      => "SJ-{$year}-" . str_pad($seq, 6, '0', STR_PAD_LEFT),
            'user_id'             => User::factory(),
            'species_id'          => Species::factory(),
            'breed_id'            => null,
            'name'                => fake()->firstName(),
            'gender'              => fake()->randomElement(['M', 'F', 'UNKNOWN']),
            'birth_date'          => fake()->dateTimeBetween('-10 years', '-1 month'),
            'approximate_age'     => null,
            'color'               => fake()->safeColorName(),
            'size'                => fake()->randomElement(['SMALL', 'MEDIUM', 'LARGE', 'GIANT']),
            'reproductive_status' => fake()->randomElement(['INTACT', 'SPAYED', 'NEUTERED', 'UNKNOWN']),
            'distinctive_features' => fake()->optional()->sentence(),
            'status'              => 'ACTIVE',
            'notes'               => null,
        ];
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => 'LOST']);
    }

    public function forAdoption(): static
    {
        return $this->state(fn () => ['status' => 'FOR_ADOPTION']);
    }

    public function deceased(): static
    {
        return $this->state(fn () => [
            'status'       => 'DECEASED',
            'death_date'   => fake()->date(),
            'death_reason' => fake()->sentence(),
        ]);
    }
}

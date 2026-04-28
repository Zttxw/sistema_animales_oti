<?php

namespace Database\Factories;

use App\Models\StrayAnimal;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StrayAnimalFactory extends Factory
{
    protected $model = StrayAnimal::class;

    public function definition(): array
    {
        $year = date('Y');
        $seq  = fake()->unique()->numberBetween(1, 999999);

        return [
            'code'          => "SJ-C-{$year}-" . str_pad($seq, 6, '0', STR_PAD_LEFT),
            'species_id'    => Species::factory(),
            'breed_id'      => null,
            'approx_gender' => fake()->randomElement(['M', 'F', 'UNKNOWN']),
            'color'         => fake()->safeColorName(),
            'size'          => fake()->randomElement(['SMALL', 'MEDIUM', 'LARGE', 'GIANT']),
            'location'      => fake()->address(),
            'status'        => 'OBSERVED',
            'notes'         => fake()->optional()->sentence(),
            'reported_by'   => User::factory(),
        ];
    }
}

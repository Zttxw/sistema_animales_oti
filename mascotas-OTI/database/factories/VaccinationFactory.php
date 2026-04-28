<?php

namespace Database\Factories;

use App\Models\Vaccination;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccinationFactory extends Factory
{
    protected $model = Vaccination::class;

    public function definition(): array
    {
        return [
            'animal_id'     => Animal::factory(),
            'vaccine_name'  => fake()->randomElement(['Rabia', 'Parvovirus', 'Moquillo', 'Triple Felina']),
            'applied_at'    => fake()->date(),
            'next_dose_at'  => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
            'notes'         => fake()->optional()->sentence(),
            'registered_by' => User::factory(),
        ];
    }
}

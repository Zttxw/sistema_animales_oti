<?php

namespace Database\Factories;

use App\Models\AnimalHistory;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalHistoryFactory extends Factory
{
    protected $model = AnimalHistory::class;

    public function definition(): array
    {
        return [
            'animal_id'   => Animal::factory(),
            'user_id'     => User::factory(),
            'change_type' => fake()->randomElement(['STATUS', 'VACCINE', 'PROCEDURE', 'ADOPTION', 'DATA']),
            'description' => fake()->sentence(),
        ];
    }
}

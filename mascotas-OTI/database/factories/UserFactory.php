<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name'         => fake()->firstName(),
            'last_name'          => fake()->lastName(),
            'identity_document'  => fake()->unique()->numerify('########'),
            'birth_date'         => fake()->date('Y-m-d', '-18 years'),
            'gender'             => fake()->randomElement(['M', 'F', 'O']),
            'phone'              => fake()->phoneNumber(),
            'email'              => fake()->unique()->safeEmail(),
            'address'            => fake()->address(),
            'sector'             => fake()->citySuffix(),
            'password'           => Hash::make('password'),
            'status'             => 'ACTIVE',
            'remember_token'     => Str::random(10),
        ];
    }

    /**
     * Indicate the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'SUSPENDED']);
    }

    /**
     * Indicate the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'INACTIVE']);
    }
}

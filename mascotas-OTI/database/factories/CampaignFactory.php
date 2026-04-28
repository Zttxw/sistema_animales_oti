<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'title'            => fake()->sentence(4),
            'campaign_type_id' => CampaignType::factory(),
            'description'      => fake()->paragraph(),
            'scheduled_at'     => fake()->dateTimeBetween('+1 week', '+3 months'),
            'location'         => fake()->address(),
            'capacity'         => fake()->numberBetween(20, 200),
            'status'           => 'DRAFT',
            'target_audience'  => null,
            'requirements'     => null,
            'created_by'       => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'PUBLISHED']);
    }
}

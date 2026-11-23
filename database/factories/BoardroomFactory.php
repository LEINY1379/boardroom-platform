<?php

namespace Database\Factories;

use App\Models\Boardroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Boardroom>
 */
class BoardroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'capacity' => fake()->numberBetween(4, 30),
            'location' => fake()->buildingNumber().' '.fake()->streetName(),
            'status' => 'Available',
            'amenities' => fake()->words(3, true),
        ];
    }
}

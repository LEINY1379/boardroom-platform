<?php

namespace Database\Factories;

use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'boardroom_id' => Boardroom::factory(),
            'created_by' => User::factory(),
            'meeting_date' => fake()->dateTimeBetween('today', '+30 days')->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'Scheduled',
        ];
    }
}

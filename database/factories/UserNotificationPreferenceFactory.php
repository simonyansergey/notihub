<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'channel' => $this->faker->randomElement(NotificationChannel::cases()),
            'enabled' => true,
        ];
    }

    public function disabled(): static
    {
        return $this->state(['enabled' => false]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'channel'   => $this->faker->randomElement(NotificationChannel::cases()),
            'payload'   => ['subject' => $this->faker->sentence(), 'body' => $this->faker->paragraph()],
            'status'    => NotificationStatus::Pending,
            'attempts'  => 1,
            'sent_at'   => null,
            'failed_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state([
            'status'  => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status'    => NotificationStatus::Failed,
            'failed_at' => now(),
        ]);
    }
}

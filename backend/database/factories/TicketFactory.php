<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\ParkingSpace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->bothify('???###')),
            'vehicle_type' => fake()->randomElement(['auto', 'moto', 'camioneta']),
            'entry_time' => now(),
            'exit_time' => null,
            'parking_space_id' => ParkingSpace::factory(),
            'status' => 'activo',
            'user_id' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finalizado',
            'exit_time' => now(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'activo',
            'exit_time' => null,
        ]);
    }

    public function auto(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'auto',
        ]);
    }

    public function moto(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'moto',
        ]);
    }

    public function camioneta(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type' => 'camioneta',
        ]);
    }
}

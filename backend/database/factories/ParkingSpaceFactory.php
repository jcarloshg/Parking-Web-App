<?php

namespace Database\Factories;

use App\Models\ParkingSpace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSpaceFactory extends Factory
{
    protected $model = ParkingSpace::class;

    public function definition(): array
    {
        return [
            'number' => fake()->unique()->bothify('??##'),
            'type' => fake()->randomElement(['general', 'discapacitado', 'eléctrico']),
            'status' => 'disponible',
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disponible',
        ]);
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ocupado',
        ]);
    }

    public function outOfService(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'fuera_servicio',
        ]);
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'general',
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'discapacitado',
        ]);
    }

    public function electric(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'eléctrico',
        ]);
    }
}

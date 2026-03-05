<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'total' => fake()->randomFloat(2, 10, 100),
            'payment_method' => fake()->randomElement(['efectivo', 'tarjeta']),
            'user_id' => User::factory(),
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'efectivo',
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'tarjeta',
        ]);
    }
}

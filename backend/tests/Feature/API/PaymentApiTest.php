<?php

namespace Tests\Feature\API;

use App\Models\ParkingSpace;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_payments(): void
    {
        Payment::factory()->count(5)->create();

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'total',
            ]);
    }

    public function test_can_view_single_payment(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_can_process_payment_and_complete_ticket(): void
    {
        $user = User::factory()->cajero()->create();
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);
        $ticket = Ticket::factory()->create([
            'status' => 'activo',
            'entry_time' => now()->subHours(2),
            'parking_space_id' => $space->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payments', [
                'ticket_id' => $ticket->id,
                'payment_method' => 'efectivo',
            ]);

        $response->assertStatus(201);

        $ticket->refresh();
        $this->assertEquals('finalizado', $ticket->status);
        $this->assertNotNull($ticket->exit_time);
        
        $space->refresh();
        $this->assertEquals('disponible', $space->status);
        
        $this->assertDatabaseHas('payments', [
            'ticket_id' => $ticket->id,
            'payment_method' => 'efectivo',
        ]);
    }

    public function test_calculates_fee_before_payment(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 'activo',
            'entry_time' => now()->subHours(2),
        ]);

        $response = $this->getJson("/api/payments/calculate/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total',
                    'hours',
                    'rate_per_hour',
                    'daily_rate',
                ]
            ]);
    }

    public function test_cannot_pay_already_paid_ticket(): void
    {
        $user = User::factory()->cajero()->create();
        $ticket = Ticket::factory()->create(['status' => 'finalizado']);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payments', [
                'ticket_id' => $ticket->id,
                'payment_method' => 'efectivo',
            ]);

        $response->assertStatus(400);
    }

    public function test_cannot_pay_ticket_without_active_status(): void
    {
        $user = User::factory()->cajero()->create();
        $ticket = Ticket::factory()->create(['status' => 'activo']);

        $payment = Payment::factory()->create(['ticket_id' => $ticket->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payments', [
                'ticket_id' => $ticket->id,
                'payment_method' => 'efectivo',
            ]);

        $response->assertStatus(400);
    }

    public function test_validates_payment_method(): void
    {
        $user = User::factory()->cajero()->create();
        $ticket = Ticket::factory()->create(['status' => 'activo']);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payments', [
                'ticket_id' => $ticket->id,
                'payment_method' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_validates_ticket_exists(): void
    {
        $user = User::factory()->cajero()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/payments', [
                'ticket_id' => 99999,
                'payment_method' => 'efectivo',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_id']);
    }

    public function test_requires_authentication(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'activo']);

        $response = $this->postJson('/api/payments', [
            'ticket_id' => $ticket->id,
            'payment_method' => 'efectivo',
        ]);

        $response->assertStatus(401);
    }

    public function test_today_payments_endpoint(): void
    {
        Payment::factory()->count(3)->create(['paid_at' => now()]);
        Payment::factory()->count(2)->create(['paid_at' => now()->subDay()]);

        $response = $this->getJson('/api/payments/today');

        $response->assertStatus(200);
    }
}

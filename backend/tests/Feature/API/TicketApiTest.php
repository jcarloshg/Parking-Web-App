<?php

namespace Tests\Feature\API;

use App\Models\ParkingSpace;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tickets(): void
    {
        Ticket::factory()->count(5)->create();

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'total',
            ]);
    }

    public function test_can_view_single_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ticket->id);
    }

    public function test_can_register_vehicle_entry(): void
    {
        $user = User::factory()->cajero()->create();
        $space = ParkingSpace::factory()->create(['status' => 'disponible']);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/tickets', [
                'plate_number' => 'ABC-1234',
                'vehicle_type' => 'auto',
                'parking_space_id' => $space->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.plate_number', 'ABC-1234');
        
        $this->assertDatabaseHas('tickets', [
            'plate_number' => 'ABC-1234',
            'status' => 'activo',
        ]);
    }

    public function test_parking_space_status_changes_to_ocupado(): void
    {
        $user = User::factory()->cajero()->create();
        $space = ParkingSpace::factory()->create(['status' => 'disponible']);

        $this->actingAs($user, 'api')
            ->postJson('/api/tickets', [
                'plate_number' => 'ABC-1234',
                'vehicle_type' => 'auto',
                'parking_space_id' => $space->id,
            ]);

        $space->refresh();
        $this->assertEquals('ocupado', $space->status);
    }

    public function test_cannot_register_on_unavailable_space(): void
    {
        $user = User::factory()->cajero()->create();
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/tickets', [
                'plate_number' => 'ABC-1234',
                'vehicle_type' => 'auto',
                'parking_space_id' => $space->id,
            ]);

        $response->assertStatus(500);
    }

    public function test_search_ticket_by_plate(): void
    {
        Ticket::factory()->create(['plate_number' => 'ABC-1234']);
        Ticket::factory()->create(['plate_number' => 'XYZ-5678']);

        $response = $this->getJson('/api/tickets/search?plate=ABC');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.plate_number', 'ABC-1234');
    }

    public function test_returns_only_active_tickets(): void
    {
        Ticket::factory()->count(3)->create(['status' => 'activo']);
        Ticket::factory()->count(2)->create(['status' => 'finalizado']);

        $response = $this->getJson('/api/tickets/active');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_validates_plate_format(): void
    {
        $user = User::factory()->cajero()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/tickets', [
                'plate_number' => 'invalid!!',
                'vehicle_type' => 'auto',
                'parking_space_id' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plate_number']);
    }

    public function test_validates_vehicle_type(): void
    {
        $user = User::factory()->cajero()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/tickets', [
                'plate_number' => 'ABC-1234',
                'vehicle_type' => 'invalid',
                'parking_space_id' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vehicle_type']);
    }

    public function test_calculate_fee_for_active_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'activo']);

        $response = $this->getJson("/api/tickets/{$ticket->id}/calculate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'entry_time',
                    'exit_time',
                    'hours',
                    'minutes',
                    'rate_per_hour',
                    'total',
                ]
            ]);
    }

    public function test_cannot_calculate_fee_for_completed_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'finalizado']);

        $response = $this->getJson("/api/tickets/{$ticket->id}/calculate");

        $response->assertStatus(400);
    }

    public function test_can_checkout_ticket(): void
    {
        $user = User::factory()->cajero()->create();
        $ticket = Ticket::factory()->create([
            'status' => 'activo',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/tickets/{$ticket->id}/checkout", [
                'payment_method' => 'efectivo',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'finalizado');

        $this->assertDatabaseHas('payments', [
            'ticket_id' => $ticket->id,
            'payment_method' => 'efectivo',
        ]);
    }

    public function test_parking_space_becomes_available_after_checkout(): void
    {
        $user = User::factory()->cajero()->create();
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);
        $ticket = Ticket::factory()->create([
            'status' => 'activo',
            'parking_space_id' => $space->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user, 'api')
            ->postJson("/api/tickets/{$ticket->id}/checkout", [
                'payment_method' => 'efectivo',
            ]);

        $space->refresh();
        $this->assertEquals('disponible', $space->status);
    }

    public function test_requires_authentication_for_creating_ticket(): void
    {
        $space = ParkingSpace::factory()->create();

        $response = $this->postJson('/api/tickets', [
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'auto',
            'parking_space_id' => $space->id,
        ]);

        $response->assertStatus(401);
    }
}

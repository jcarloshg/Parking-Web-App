<?php

namespace Tests\Unit\Services;

use App\Models\ParkingSpace;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketService = new TicketService();
    }

    public function test_can_get_all_tickets(): void
    {
        Ticket::factory()->count(5)->create();

        $tickets = $this->ticketService->getAll();

        $this->assertCount(5, $tickets);
    }

    public function test_can_get_ticket_by_id(): void
    {
        $ticket = Ticket::factory()->create();

        $found = $this->ticketService->getById($ticket->id);

        $this->assertEquals($ticket->id, $found->id);
    }

    public function test_can_get_active_tickets(): void
    {
        Ticket::factory()->count(3)->create(['status' => 'activo']);
        Ticket::factory()->count(2)->create(['status' => 'finalizado']);

        $activeTickets = $this->ticketService->getActive();

        $this->assertCount(3, $activeTickets);
    }

    public function test_can_search_tickets_by_plate(): void
    {
        Ticket::factory()->create(['plate_number' => 'ABC-1234']);
        Ticket::factory()->create(['plate_number' => 'XYZ-5678']);

        $results = $this->ticketService->searchByPlate('ABC');

        $this->assertCount(1, $results);
        $this->assertEquals('ABC-1234', $results->first()->plate_number);
    }

    public function test_can_create_ticket_and_mark_space_occupied(): void
    {
        $user = User::factory()->create();
        $space = ParkingSpace::factory()->create(['status' => 'disponible']);

        $ticket = $this->ticketService->create([
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'auto',
            'parking_space_id' => $space->id,
        ], $user->id);

        $this->assertEquals('ABC-1234', $ticket->plate_number);
        $this->assertEquals('activo', $ticket->status);

        $space->refresh();
        $this->assertEquals('ocupado', $space->status);
    }

    public function test_cannot_create_ticket_on_occupied_space(): void
    {
        $user = User::factory()->create();
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El espacio de estacionamiento no está disponible');

        $this->ticketService->create([
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'auto',
            'parking_space_id' => $space->id,
        ], $user->id);
    }

    public function test_can_calculate_fee(): void
    {
        $ticket = Ticket::factory()->create([
            'vehicle_type' => 'auto',
            'status' => 'activo',
        ]);

        $feeData = $this->ticketService->calculateFee($ticket);

        $this->assertArrayHasKey('total', $feeData);
        $this->assertArrayHasKey('hours', $feeData);
        $this->assertArrayHasKey('rate_per_hour', $feeData);
        $this->assertEquals(20, $feeData['rate_per_hour']);
    }

    public function test_calculate_fee_moto_rate(): void
    {
        $ticket = Ticket::factory()->create([
            'vehicle_type' => 'moto',
            'status' => 'activo',
        ]);

        $feeData = $this->ticketService->calculateFee($ticket);

        $this->assertEquals(10, $feeData['rate_per_hour']);
    }

    public function test_calculate_fee_camioneta_rate(): void
    {
        $ticket = Ticket::factory()->create([
            'vehicle_type' => 'camioneta',
            'status' => 'activo',
        ]);

        $feeData = $this->ticketService->calculateFee($ticket);

        $this->assertEquals(30, $feeData['rate_per_hour']);
    }

    public function test_can_checkout_ticket(): void
    {
        $user = User::factory()->create();
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);
        $ticket = Ticket::factory()->create([
            'status' => 'activo',
            'parking_space_id' => $space->id,
            'user_id' => $user->id,
        ]);

        $checkedOutTicket = $this->ticketService->checkout($ticket, 'efectivo');

        $this->assertEquals('finalizado', $checkedOutTicket->status);
        $this->assertNotNull($checkedOutTicket->exit_time);
        $this->assertNotNull($checkedOutTicket->payment);

        $space->refresh();
        $this->assertEquals('disponible', $space->status);
    }

    public function test_cannot_checkout_completed_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'finalizado']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El ticket no está activo');

        $this->ticketService->checkout($ticket, 'efectivo');
    }

    public function test_find_available_space(): void
    {
        ParkingSpace::factory()->create(['status' => 'disponible']);
        ParkingSpace::factory()->create(['status' => 'ocupado']);

        $space = $this->ticketService->findAvailableSpace();

        $this->assertNotNull($space);
        $this->assertEquals('disponible', $space->status);
    }
}

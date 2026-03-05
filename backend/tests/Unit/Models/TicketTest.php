<?php

namespace Tests\Unit\Models;

use App\Models\Ticket;
use App\Models\ParkingSpace;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_belongs_to_parking_space(): void
    {
        $space = ParkingSpace::factory()->create();
        $ticket = Ticket::factory()->for($space, 'parkingSpace')->create();

        $this->assertEquals($space->id, $ticket->parkingSpace->id);
    }

    public function test_ticket_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user, 'user')->create();

        $this->assertEquals($user->id, $ticket->user->id);
    }

    public function test_ticket_has_one_payment(): void
    {
        $ticket = Ticket::factory()->create();
        $payment = Payment::factory()->for($ticket, 'ticket')->create();

        $this->assertEquals($ticket->id, $payment->ticket->id);
    }

    public function test_active_scope_returns_only_active_tickets(): void
    {
        Ticket::factory()->count(3)->active()->create();
        Ticket::factory()->count(2)->completed()->create();

        $this->assertCount(3, Ticket::active()->get());
    }

    public function test_completed_scope_returns_only_completed_tickets(): void
    {
        Ticket::factory()->count(3)->active()->create();
        Ticket::factory()->count(2)->completed()->create();

        $this->assertCount(2, Ticket::completed()->get());
    }

    public function test_is_active_returns_true_when_activo(): void
    {
        $ticket = Ticket::factory()->active()->create();

        $this->assertTrue($ticket->isActive());
        $this->assertFalse($ticket->isCompleted());
    }

    public function test_is_completed_returns_true_when_finalizado(): void
    {
        $ticket = Ticket::factory()->completed()->create();

        $this->assertTrue($ticket->isCompleted());
        $this->assertFalse($ticket->isActive());
    }

    public function test_ticket_has_correct_fillable_attributes(): void
    {
        $ticket = new Ticket();
        $fillable = $ticket->getFillable();

        $this->assertContains('plate_number', $fillable);
        $this->assertContains('vehicle_type', $fillable);
        $this->assertContains('entry_time', $fillable);
        $this->assertContains('parking_space_id', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('user_id', $fillable);
    }
}

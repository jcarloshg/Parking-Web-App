<?php

namespace Tests\Unit\Models;

use App\Models\ParkingSpace;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parking_space_has_many_tickets(): void
    {
        $space = ParkingSpace::factory()->create();
        $ticket = Ticket::factory()->for($space, 'parkingSpace')->create();

        $this->assertTrue($space->tickets->contains($ticket));
    }

    public function test_available_scope_returns_only_available_spaces(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $this->assertCount(3, ParkingSpace::available()->get());
    }

    public function test_occupied_scope_returns_only_occupied_spaces(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $this->assertCount(2, ParkingSpace::occupied()->get());
    }

    public function test_out_of_service_scope_returns_only_out_of_service_spaces(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'fuera_servicio']);

        $this->assertCount(2, ParkingSpace::outOfService()->get());
    }

    public function test_is_available_returns_true_when_disponible(): void
    {
        $space = ParkingSpace::factory()->available()->create();

        $this->assertTrue($space->isAvailable());
        $this->assertFalse($space->isOccupied());
    }

    public function test_is_occupied_returns_true_when_ocupado(): void
    {
        $space = ParkingSpace::factory()->occupied()->create();

        $this->assertTrue($space->isOccupied());
        $this->assertFalse($space->isAvailable());
    }

    public function test_parking_space_has_correct_fillable_attributes(): void
    {
        $space = new ParkingSpace();
        $fillable = $space->getFillable();

        $this->assertContains('number', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_parking_space_number_must_be_unique(): void
    {
        ParkingSpace::factory()->create(['number' => 'A1']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ParkingSpace::factory()->create(['number' => 'A1']);
    }
}

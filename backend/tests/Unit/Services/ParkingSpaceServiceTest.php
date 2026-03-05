<?php

namespace Tests\Unit\Services;

use App\Models\ParkingSpace;
use App\Services\ParkingSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpaceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ParkingSpaceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ParkingSpaceService();
    }

    public function test_get_all_returns_paginated_spaces(): void
    {
        ParkingSpace::factory()->count(5)->create();

        $result = $this->service->getAll(2);

        $this->assertCount(2, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_get_available_returns_only_available_spaces(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $result = $this->service->getAvailable();

        $this->assertCount(3, $result);
    }

    public function test_get_available_count_returns_correct_count(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $count = $this->service->getAvailableCount();

        $this->assertEquals(3, $count);
    }

    public function test_get_by_id_returns_space(): void
    {
        $space = ParkingSpace::factory()->create();

        $result = $this->service->getById($space->id);

        $this->assertEquals($space->id, $result->id);
    }

    public function test_get_by_id_returns_null_for_invalid_id(): void
    {
        $result = $this->service->getById(999);

        $this->assertNull($result);
    }

    public function test_create_space(): void
    {
        $data = [
            'number' => 'A1',
            'type' => 'general',
            'status' => 'disponible',
        ];

        $result = $this->service->create($data);

        $this->assertEquals('A1', $result->number);
        $this->assertDatabaseHas('parking_spaces', ['number' => 'A1']);
    }

    public function test_update_space(): void
    {
        $space = ParkingSpace::factory()->create(['number' => 'A1']);

        $result = $this->service->update($space, ['number' => 'A2']);

        $this->assertEquals('A2', $result->number);
    }

    public function test_delete_space(): void
    {
        $space = ParkingSpace::factory()->create();

        $this->service->delete($space);

        $this->assertDatabaseMissing('parking_spaces', ['id' => $space->id]);
    }

    public function test_set_status(): void
    {
        $space = ParkingSpace::factory()->create(['status' => 'disponible']);

        $result = $this->service->setStatus($space, 'ocupado');

        $this->assertEquals('ocupado', $result->status);
    }

    public function test_is_available_returns_true_for_disponible(): void
    {
        $space = ParkingSpace::factory()->create(['status' => 'disponible']);

        $this->assertTrue($this->service->isAvailable($space));
    }

    public function test_is_available_returns_false_for_ocupado(): void
    {
        $space = ParkingSpace::factory()->create(['status' => 'ocupado']);

        $this->assertFalse($this->service->isAvailable($space));
    }
}

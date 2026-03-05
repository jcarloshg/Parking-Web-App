<?php

namespace Tests\Feature\API;

use App\Models\ParkingSpace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpaceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_parking_spaces(): void
    {
        ParkingSpace::factory()->count(5)->create();

        $response = $this->getJson('/api/parking-spaces');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'total',
            ]);
    }

    public function test_can_view_single_parking_space(): void
    {
        $space = ParkingSpace::factory()->create();

        $response = $this->getJson("/api/parking-spaces/{$space->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $space->id);
    }

    public function test_admin_can_create_parking_space(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/parking-spaces', [
                'number' => 'C1',
                'type' => 'general',
                'status' => 'disponible',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.number', 'C1');

        $this->assertDatabaseHas('parking_spaces', [
            'number' => 'C1',
            'type' => 'general',
        ]);
    }

    public function test_cajero_cannot_create_parking_space(): void
    {
        $cajero = User::factory()->cajero()->create();

        $response = $this->actingAs($cajero, 'api')
            ->postJson('/api/parking-spaces', [
                'number' => 'C1',
                'type' => 'general',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_parking_space(): void
    {
        $admin = User::factory()->admin()->create();
        $space = ParkingSpace::factory()->create(['number' => 'A1']);

        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/parking-spaces/{$space->id}", [
                'number' => 'A2',
                'type' => 'discapacitado',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.number', 'A2');
    }

    public function test_admin_can_delete_parking_space(): void
    {
        $admin = User::factory()->admin()->create();
        $space = ParkingSpace::factory()->create();

        $response = $this->actingAs($admin, 'api')
            ->deleteJson("/api/parking-spaces/{$space->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('parking_spaces', [
            'id' => $space->id,
        ]);
    }

    public function test_returns_available_spaces(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $response = $this->getJson('/api/parking-spaces/available');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_returns_available_count(): void
    {
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

        $response = $this->getJson('/api/parking-spaces/available-count');

        $response->assertStatus(200)
            ->assertJsonPath('available_count', 3);
    }

    public function test_validates_unique_number(): void
    {
        ParkingSpace::factory()->create(['number' => 'A1']);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/parking-spaces', [
                'number' => 'A1',
                'type' => 'general',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number']);
    }

    public function test_validates_invalid_type(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/parking-spaces', [
                'number' => 'A1',
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_validates_number_format(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/parking-spaces', [
                'number' => 'invalid@number!',
                'type' => 'general',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number']);
    }

    public function test_pagination_works(): void
    {
        ParkingSpace::factory()->count(20)->create();

        $response = $this->getJson('/api/parking-spaces?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20);
    }
}

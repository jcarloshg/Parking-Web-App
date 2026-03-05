<?php

namespace Tests\Feature\API;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\ParkingSpace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_daily_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Payment::factory()->count(5)->create([
            'paid_at' => now(),
        ]);
        ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
        Ticket::factory()->count(2)->create(['status' => 'activo']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/reports/daily');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_ingresos',
                'tickets_atendidos',
                'promedio_por_ticket',
                'cajones_disponibles',
                'tickets_activos',
                'fecha',
            ]);
    }

    public function test_supervisor_can_view_reports(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $response = $this->actingAs($supervisor, 'api')
            ->getJson('/api/reports/daily');

        $response->assertStatus(200);
    }

    public function test_cajero_cannot_view_reports(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        $response = $this->actingAs($cajero, 'api')
            ->getJson('/api/reports/daily');

        $response->assertStatus(403);
    }

    public function test_monthly_report_includes_daily_breakdown(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/reports/monthly');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ingresos_por_día',
                'hora_pica',
                'tipo_vehiculo_frecuente',
                'total_ingresos_mes',
                'total_tickets_mes',
                'año',
                'mes',
            ]);
    }

    public function test_dashboard_summary_returns_latest_tickets(): void
    {
        Ticket::factory()->count(10)->create();
        ParkingSpace::factory()->count(5)->create(['status' => 'disponible']);
        
        $response = $this->getJson('/api/reports/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cajones_disponibles',
                'ingresos_dia',
                'tickets_activos',
                'ultimos_tickets',
            ]);
    }

    public function test_dashboard_summary_returns_5_latest_tickets(): void
    {
        Ticket::factory()->count(10)->create();
        ParkingSpace::factory()->count(5)->create(['status' => 'disponible']);
        
        $response = $this->getJson('/api/reports/summary');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('ultimos_tickets'));
    }

    public function test_daily_report_with_custom_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Payment::factory()->create([
            'paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/reports/daily?date=' . now()->toDateString());

        $response->assertStatus(200);
    }

    public function test_monthly_report_with_custom_year_and_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/reports/monthly?year=2024&month=1');

        $response->assertStatus(200)
            ->assertJsonPath('año', 2024)
            ->assertJsonPath('mes', 1);
    }

    public function test_unauthenticated_user_cannot_access_daily_report(): void
    {
        $response = $this->getJson('/api/reports/daily');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_access_monthly_report(): void
    {
        $response = $this->getJson('/api/reports/monthly');

        $response->assertStatus(401);
    }

    public function test_daily_report_calculates_correct_averages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Payment::factory()->count(4)->create([
            'total' => 100,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/reports/daily');

        $response->assertStatus(200)
            ->assertJsonPath('total_ingresos', 400)
            ->assertJsonPath('tickets_atendidos', 4)
            ->assertJsonPath('promedio_por_ticket', 100);
    }
}

# Phase 7: Reports API

## Objetivo
Implementar reportes diarios y mensuales.

## Tareas

### 7.1 ReportController
- [ ] daily() → reporte del día actual
- [ ] monthly() → reporte del mes actual
- [ ] summary() → resumen para dashboard

### 7.2 Rutas API
```
GET /api/reports/daily
GET /api/reports/monthly
GET /api/reports/summary
```

### 7.3 Reporte Diario
Retornar:
- [ ] total_ingresos (suma de pagos hoy)
- [ ] tickets_atendidos (count pagos hoy)
- [ ] promedio_por_ticket (total/tickets)
- [ ] cajones_disponibles (count)
- [ ] tickets_activos (count)

### 7.4 Reporte Mensual
Retornar:
- [ ] ingresos_por_día (array día → monto)
- [ ] hora_pica (hora con más tickets)
- [ ] tipo_vehiculo_frecuente (count por tipo)
- [ ] total_ingresos_mes
- [ ] total_tickets_mes

### 7.5 Resumen Dashboard
- [ ] cajones_disponibles
- [ ] ingresos_dia
- [ ] tickets_activos
- [ ] ultimos_tickets (últimos 5)

### 7.6 Service/Repository
- [ ] ReportService para lógica de reportes

### 7.7 Policies
- [ ] Admin y Supervisor pueden ver reportes

## Testing

### Pruebas de Reports
```bash
./vendor/bin/phpunit --filter=ReportApiTest
./vendor/bin/phpunit --filter=ReportServiceTest
```

### Cobertura Objetivo
- ReportController: 85%
- ReportService: 80%

### Tests Requeridos
- Reporte diario con datos correctos
- Reporte mensual con agregaciones
- Summary para dashboard
- Permisos por rol (Admin/Supervisor)
- Cálculo de ingresos

### Estructura de Tests
```
tests/Feature/API/
├── ReportApiTest.php
tests/Unit/Services/
└── ReportServiceTest.php
```

### Ejemplo: ReportApiTest
```php
public function test_admin_can_view_daily_report()
{
    $admin = User::factory()->create(['role' => 'admin']);
    
    Payment::factory()->count(5)->create([
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/reports/daily');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total_ingresos',
            'tickets_atendidos',
            'promedio_por_ticket',
            'cajones_disponibles',
            'tickets_activos',
        ]);
}

public function test_supervisor_can_view_reports()
{
    $supervisor = User::factory()->create(['role' => 'supervisor']);

    $response = $this->actingAs($supervisor, 'api')
        ->getJson('/api/reports/daily');

    $response->assertStatus(200);
}

public function test_cajero_cannot_view_reports()
{
    $cajero = User::factory()->create(['role' => 'cajero']);

    $response = $this->actingAs($cajero, 'api')
        ->getJson('/api/reports/daily');

    $response->assertStatus(403);
}

public function test_monthly_report_includes_daily_breakdown()
{
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/reports/monthly');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'ingresos_por_día',
            'total_ingresos_mes',
            'total_tickets_mes',
        ]);
}

public function test_dashboard_summary_returns_latest_tickets()
{
    Ticket::factory()->count(10)->create();
    
    $response = $this->getJson('/api/reports/summary');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'cajones_disponibles',
            'ingresos_dia',
            'tickets_activos',
            'ultimos_tickets',
        ])
        ->assertJsonCount(5, 'ultimos_tickets');
}
```

## Entregables
- Reporte diario completo
- Reporte mensual completo
- Dashboard summary

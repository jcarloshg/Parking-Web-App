# Phase 5: Tickets API

## Objetivo
Implementar registro de entradas de vehículos.

## Tareas

### 5.1 TicketController
- [ ] index() → listar tickets (paginado)
- [ ] store() → crear ticket (entrada)
- [ ] show() → ver ticket específico
- [ ] active() → listar tickets activos
- [ ] search() → buscar por placa

### 5.2 Rutas API
```
GET    /api/tickets
GET    /api/tickets/{id}
POST   /api/tickets
GET    /api/tickets/active
GET    /api/tickets/search?plate=XXX
```

### 5.3 Registro de Entrada
- [ ] Validar placa (formato)
- [ ] Seleccionar tipo vehículo (auto, moto, camioneta)
- [ ] Asignar cajón automáticamente o manual
- [ ] Guardar entry_time = now()
- [ ] Cambiar status cajón a 'ocupado'

### 5.4 Validaciones
- plate_number: required|string|regex:/^[A-Z0-9-]+$/i
- vehicle_type: required|in:auto,moto,camioneta
- parking_space_id: required|exists:parking_spaces,id

### 5.5 Service/Repository
- [ ] TicketService para lógica de entrada

### 5.6 Policies
- [ ] Admin, Cajero, Supervisor pueden registrar entrada

## Testing

### Pruebas de Tickets
```bash
./vendor/bin/phpunit --filter=TicketApiTest
./vendor/bin/phpunit --filter=TicketServiceTest
```

### Cobertura Objetivo
- TicketController: 85%
- TicketService: 90%

### Tests Requeridos
- Crear ticket de entrada
- Buscar ticket por placa
- Listar tickets activos
- Asignación automática de cajón
- Cambio de status del cajón
- Validación de formato de placa

### Estructura de Tests
```
tests/Feature/API/
├── TicketApiTest.php
tests/Unit/Services/
└── TicketServiceTest.php
```

### Ejemplo: TicketApiTest
```php
public function test_can_register_vehicle_entry()
{
    $cajero = User::factory()->create(['role' => 'cajero']);
    $space = ParkingSpace::factory()->create(['status' => 'disponible']);

    $response = $this->actingAs($cajero, 'api')
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

public function test_parking_space_status_changes_to_ocupado()
{
    $space = ParkingSpace::factory()->create(['status' => 'disponible']);
    
    Ticket::factory()->create(['parking_space_id' => $space->id]);

    $space->refresh();
    $this->assertEquals('ocupado', $space->status);
}

public function test_search_ticket_by_plate()
{
    Ticket::factory()->create(['plate_number' => 'ABC-1234']);
    Ticket::factory()->create(['plate_number' => 'XYZ-5678']);

    $response = $this->getJson('/api/tickets/search?plate=ABC');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.plate_number', 'ABC-1234');
}

public function test_returns_only_active_tickets()
{
    Ticket::factory()->count(3)->create(['status' => 'activo']);
    Ticket::factory()->count(2)->create(['status' => 'finalizado']);

    $response = $this->getJson('/api/tickets/active');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
}

public function test_validates_plate_format()
{
    $response = $this->postJson('/api/tickets', [
        'plate_number' => 'invalid!!',
        'vehicle_type' => 'auto',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plate_number']);
}
```

## Entregables
- Registro de entrada funcionando
- Búsqueda de tickets por placa
- Tickets activos listados
- Cajón marcado como ocupado

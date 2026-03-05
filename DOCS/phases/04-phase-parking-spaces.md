# Phase 4: Parking Spaces API

## Objetivo
Implementar CRUD de cajones de estacionamiento.

## Tareas

### 4.1 ParkingSpaceController
- [ ] index() → listar todos (paginado)
- [ ] store() → crear cajón
- [ ] show() → ver cajón específico
- [ ] update() → actualizar cajón
- [ ] destroy() → eliminar cajón
- [ ] available() → listar cajones disponibles

### 4.2 Rutas API
```
GET    /api/parking-spaces
GET    /api/parking-spaces/{id}
POST   /api/parking-spaces
PUT    /api/parking-spaces/{id}
DELETE /api/parking-spaces/{id}
GET    /api/parking-spaces/available
```

### 4.3 Validaciones
- number: required|string|unique:parking_spaces|regex:/^[A-Z0-9-]+$/i
- type: required|in:general,discapacitado,eléctrico
- status: required|in:disponible,ocupado,fuera_servicio

### 4.4 Service/Repository
- [ ] ParkingSpaceService para lógica de negocio

### 4.5 Policies (Permisos)
- [ ] Admin puede gestionar cajones
- [ ] Cajero/Supervisor solo ver

## Testing

### Pruebas de Parking Spaces
```bash
./vendor/bin/phpunit --filter=ParkingSpaceApiTest
./vendor/bin/phpunit --filter=ParkingSpaceServiceTest
```

### Cobertura Objetivo
- ParkingSpaceController: 85%
- ParkingSpaceService: 80%

### Tests Requeridos
- CRUD completo de cajones
- Listado con paginación
- Validación de uniqueness
- Filtro de cajones disponibles
- Policy: Admin puede crear/editar/eliminar
- Policy: Cajero/Supervisor solo lectura

### Estructura de Tests
```
tests/Feature/API/
├── ParkingSpaceApiTest.php
tests/Unit/Services/
└── ParkingSpaceServiceTest.php
```

### Ejemplo: ParkingSpaceApiTest
```php
public function test_admin_can_create_parking_space()
{
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin, 'api')
        ->postJson('/api/parking-spaces', [
            'number' => 'A1',
            'type' => 'general',
            'status' => 'disponible',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.number', 'A1');
}

public function test_cajero_cannot_create_parking_space()
{
    $cajero = User::factory()->create(['role' => 'cajero']);

    $response = $this->actingAs($cajero, 'api')
        ->postJson('/api/parking-spaces', [
            'number' => 'A1',
            'type' => 'general',
        ]);

    $response->assertStatus(403);
}

public function test_returns_available_spaces()
{
    ParkingSpace::factory()->count(3)->create(['status' => 'disponible']);
    ParkingSpace::factory()->count(2)->create(['status' => 'ocupado']);

    $response = $this->getJson('/api/parking-spaces/available');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
}

public function test_validates_unique_number()
{
    ParkingSpace::factory()->create(['number' => 'A1']);

    $response = $this->postJson('/api/parking-spaces', [
        'number' => 'A1',
        'type' => 'general',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['number']);
}
```

## Entregables
- CRUD completo de cajones
- Endpoint de cajones disponibles
- Validaciones implementadas

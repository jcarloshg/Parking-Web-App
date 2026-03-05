# Phase 6: Payments API

## Objetivo
Implementar registro de salidas y pagos.

## Tareas

### 6.1 PaymentController
- [ ] index() → listar pagos (paginado)
- [ ] store() → crear pago
- [ ] show() → ver pago específico
- [ ] today() → pagos de hoy
- [ ] calculate() → calcular tarifa sin pagar

### 6.2 Rutas API
```
GET    /api/payments
GET    /api/payments/{id}
POST   /api/payments
GET    /api/payments/today
GET    /api/payments/calculate/{ticket_id}
```

### 6.3 Cálculo de Tarifa
Implementar lógica de cobros:

| Tipo | Hora | Diaria |
|------|------|--------|
| auto | $20 | $150 |
| moto | $10 | $80 |
| camioneta | $30 | $200 |

**Condiciones:**
- [ ] Tolerancia: 10 minutos = $0
- [ ] Cobro por hora completa
- [ ] Más de 24h = tarifa diaria

### 6.4 Registro de Salida
- [ ] Buscar ticket activo por placa
- [ ] Calcular total
- [ ] Registrar pago
- [ ] Actualizar exit_time = now()
- [ ] Cambiar status ticket a 'finalizado'
- [ ] Cambiar status cajón a 'disponible'

### 6.5 Validaciones
- ticket_id: required|exists:tickets,id
- total: required|numeric|min:0
- payment_method: required|in:efectivo,tarjeta

### 6.6 Service/Repository
- [ ] PaymentService para lógica de cálculo
- [ ] TariffCalculator para tarifas

### 6.7 Policies
- [ ] Admin, Cajero, Supervisor pueden registrar salida

## Testing

### Pruebas de Payments
```bash
./vendor/bin/phpunit --filter=PaymentApiTest
./vendor/bin/phpunit --filter=PaymentServiceTest
./vendor/bin/phpunit --filter=FeeCalculatorTest
```

### Cobertura Objetivo
- PaymentController: 85%
- PaymentService: 90%
- FeeCalculator: 95% (CRÍTICO)

### Tests Requeridos (Prioridad Alta)
- Cálculo de tarifas por tipo de vehículo
- Tolerancia de 10 minutos = $0
- Cobro por hora completa
- Tarifa diaria después de 24h
- Registro de pago completa el ticket
- Liberación de cajón al pagar
- Validación de ticket activo

### Estructura de Tests
```
tests/Feature/API/
├── PaymentApiTest.php
tests/Unit/Services/
├── PaymentServiceTest.php
└── FeeCalculatorTest.php
```

### Ejemplo: FeeCalculatorTest (CRÍTICO)
```php
public function test_calculates_15_minutes_as_zero()
{
    $fee = $this->calculator->calculate('auto', now()->addMinutes(15));
    $this->assertEquals(0, $fee);
}

public function test_calculates_one_hour_for_auto()
{
    $fee = $this->calculator->calculate('auto', now()->addHour());
    $this->assertEquals(20, $fee);
}

public function test_calculates_two_hours_for_auto()
{
    $fee = $this->calculator->calculate('auto', now()->addHours(2));
    $this->assertEquals(40, $fee);
}

public function test_calculates_daily_rate_after_24h()
{
    $fee = $this->calculator->calculate('auto', now()->addHours(25));
    $this->assertEquals(150, $fee);
}

public function test_moto_has_different_rate()
{
    $fee = $this->calculator->calculate('moto', now()->addHour());
    $this->assertEquals(10, $fee);
}

public function test_camioneta_has_higher_rate()
{
    $fee = $this->calculator->calculate('camioneta', now()->addHour());
    $this->assertEquals(30, $fee);
}

public function test_rounds_up_to_next_hour()
{
    $fee = $this->calculator->calculate('auto', now()->addMinutes(65));
    $this->assertEquals(40, $fee); // 2 horas
}
```

### Ejemplo: PaymentApiTest
```php
public function test_can_process_payment_and_complete_ticket()
{
    $ticket = Ticket::factory()->create([
        'status' => 'activo',
        'entry_time' => now()->subHours(2),
    ]);
    $space = ParkingSpace::factory()->create();

    $response = $this->actingAs($this->cajero, 'api')
        ->postJson('/api/payments', [
            'ticket_id' => $ticket->id,
            'payment_method' => 'efectivo',
        ]);

    $response->assertStatus(201);

    $ticket->refresh();
    $this->assertEquals('finalizado', $ticket->status);
    $this->assertNotNull($ticket->exit_time);
    
    $space->refresh();
    $this->assertEquals('disponible', $space->status);
}

public function test_calculates_fee_before_payment()
{
    $ticket = Ticket::factory()->create([
        'entry_time' => now()->subHours(2),
    ]);

    $response = $this->getJson("/api/payments/calculate/{$ticket->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['total', 'breakdown']);
}

public function test_cannot_pay_already_paid_ticket()
{
    $ticket = Ticket::factory()->create(['status' => 'finalizado']);

    $response = $this->postJson('/api/payments', [
        'ticket_id' => $ticket->id,
        'payment_method' => 'efectivo',
    ]);

    $response->assertStatus(422);
}
```

## Entregables
- Cálculo de tarifas funcionando
- Registro de pago completo
- Liberación de cajón al pagar

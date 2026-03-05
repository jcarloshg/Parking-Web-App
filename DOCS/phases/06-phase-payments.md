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

## Entregables
- Cálculo de tarifas funcionando
- Registro de pago completo
- Liberación de cajón al pagar

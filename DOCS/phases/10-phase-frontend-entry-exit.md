# Phase 10: Frontend - Entry/Exit

## Objetivo
Implementar pantallas de registro de entrada y salida.

## Tareas

### 10.1 Entry Page (Registro de Entrada)
- [ ] Formulario entrada:
  - Placa (input text, mayúsculas)
  - Tipo vehículo (select: auto, moto, camioneta)
  - Cajón (select: solo disponibles)
- [ ] Validación frontend
- [ ] Botón "Registrar Entrada"
- [ ] Llamar POST /api/tickets
- [ ] Mostrar ticket generado (número, hora entrada)
- [ ] Mensaje de éxito/error

### 10.2 Exit Page (Registro de Salida)
- [ ] Buscador por placa
- [ ] Llamar GET /api/tickets/search?plate=XXX
- [ ] Mostrar ticket encontrado:
  - Placa
  - Tipo vehículo
  - Hora entrada
  - Tiempo transcurrido
  - **Total a pagar** (calculado)
- [ ] Formulario pago:
  - Método pago (select: efectivo, tarjeta)
- [ ] Botón "Pagar y Salir"
- [ ] Llamar POST /api/payments
- [ ] Mostrar comprobante

### 10.3 Components
- [ ] PlateInput.vue (mayúsculas automáticas)
- [ ] VehicleSelect.vue
- [ ] ParkingSpaceSelect.vue
- [ ] PaymentForm.vue
- [ ] TicketCard.vue

### 10.4 API Integration
- [ ] POST /api/tickets
- [ ] GET /api/tickets/search
- [ ] GET /api/payments/calculate/{id}
- [ ] POST /api/payments

### 10.5 Permisos
- [ ] Admin, Cajero, Supervisor: entrada y salida

## Entregables
- Registro de entrada funcionando
- Búsqueda de ticket por placa
- Cálculo y pago de tarifa
- Comprobante de pago

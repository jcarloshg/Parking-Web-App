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

## Entregables
- Registro de entrada funcionando
- Búsqueda de tickets por placa
- Tickets activos listados
- Cajón marcado como ocupado

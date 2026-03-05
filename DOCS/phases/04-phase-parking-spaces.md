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

## Entregables
- CRUD completo de cajones
- Endpoint de cajones disponibles
- Validaciones implementadas

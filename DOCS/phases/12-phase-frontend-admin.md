# Phase 12: Frontend - Administration

## Objetivo
Implementar pantallas de administración.

## Tareas

### 12.1 Users Management (Admin)
- [ ] Listado usuarios (tabla paginada)
- [ ] Columnas: nombre, email, rol, fecha creación
- [ ] Botón crear usuario
- [ ] Modal/Slide-over crear:
  - Nombre
  - Email
  - Password
  - Rol (select)
- [ ] Botón editar usuario
- [ ] Modal editar
- [ ] Botón eliminar usuario (confirmación)
- [ ] API: GET/POST/PUT/DELETE /api/users

### 12.2 Parking Spaces Management (Admin)
- [ ] Listado cajones (grid o tabla)
- [ ] Columnas: número, tipo, estado
- [ ] Botón crear cajón
- [ ] Formulario crear:
  - Número
  - Tipo (select)
- [ ] Botón editar
- [ ] Formulario editar
- [ ] Botón eliminar
- [ ] Toggle status rápido
- [ ] API: CRUD /api/parking-spaces

### 12.3 Components
- [ ] UsersTable.vue
- [ ] UserForm.vue
- [ ] ParkingSpacesTable.vue
- [ ] ParkingSpaceForm.vue
- [ ] ConfirmDialog.vue

### 12.4 Permisos
- [ ] Admin: gestión completa
- [ ] Cajero: NO acceso
- [ ] Supervisor: NO acceso

### 12.5 Navigation
- [ ] Menú Admin solo visible para admin
- [ ] Rutas protegidas server-side también

## Entregables
- CRUD completo usuarios
- CRUD completo cajones
- Solo admin tiene acceso
- Validaciones frontend

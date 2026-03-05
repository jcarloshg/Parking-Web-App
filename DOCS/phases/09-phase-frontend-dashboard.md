# Phase 9: Frontend - Dashboard

## Objetivo
Implementar pantalla principal del dashboard.

## Tareas

### 9.1 Dashboard Layout
- [ ] Sidebar con navegación
- [ ] Header con usuario y logout
- [ ] Contenido principal

### 9.2 Dashboard View
- [ ] Grid de cajones disponibles (visual)
- [ ] Cards de estadísticas:
  - Ingresos del día
  - Tickets activos
  - Cajones disponibles

### 9.3 Parking Spaces Grid
- [ ] Mostrar todos los cajones
- [ ] Color según status:
  - Verde: disponible
  - Rojo: ocupado
  - Gris: fuera_servicio
- [ ] Mostrar tipo (general, discapacitado, eléctrico)

### 9.4 Stats Cards
- [ ] Total ingresos día
- [ ] Tickets activos
- [ ] Cajones disponibles
- [ ] Últimos tickets (tabla)

### 9.5 API Integration
- [ ] Llamar /api/reports/summary
- [ ] Llamar /api/parking-spaces/available
- [ ] Refresh automático cada 30 segundos

### 9.6 Permisos
- [ ] Todos los roles ven dashboard
- [ ] Admin: acceso completo
- [ ] Cajero: entrada/salida
- [ ] Supervisor: entrada/salida + reportes

## Entregables
- Dashboard con grid de cajones
- Stats en tiempo real
- Navegación funcional

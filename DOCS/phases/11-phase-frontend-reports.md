# Phase 11: Frontend - Reports

## Objetivo
Implementar pantallas de reportes.

## Tareas

### 11.1 Reports Layout
- [ ] Tabs: Diario, Mensual
- [ ] Filtros por fecha

### 11.2 Daily Report
- [ ] Tarjetas stats:
  - Total ingresos
  - Tickets atendidos
  - Promedio por ticket
- [ ] Tabla de pagos del día
- [ ] Gráfica de ingresos por hora

### 11.3 Monthly Report
- [ ] Gráfica de ingresos por día (bar chart)
- [ ] Hora pico (line chart tickets/hora)
- [ ] Distribución tipo vehículo (pie chart)
- [ ] Tabla resumen mensual

### 11.4 Charts (Chart.js)
- [ ] Bar chart ingresos
- [ ] Line chart hora pico
- [ ] Pie chart tipos vehículo

### 11.5 API Integration
- [ ] GET /api/reports/daily
- [ ] GET /api/reports/monthly
- [ ] Manejo de loading states
- [ ] Manejo de errores

### 11.6 Permisos
- [ ] Admin: ver reportes
- [ ] Supervisor: ver reportes
- [ ] Cajero: NO puede acceder

## Entregables
- Reporte diario con stats y tabla
- Reporte mensual con gráficas
- Gráficas interactivas
- Acceso restringido por rol

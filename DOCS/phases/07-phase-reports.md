# Phase 7: Reports API

## Objetivo
Implementar reportes diarios y mensuales.

## Tareas

### 7.1 ReportController
- [ ] daily() → reporte del día actual
- [ ] monthly() → reporte del mes actual
- [ ] summary() → resumen para dashboard

### 7.2 Rutas API
```
GET /api/reports/daily
GET /api/reports/monthly
GET /api/reports/summary
```

### 7.3 Reporte Diario
Retornar:
- [ ] total_ingresos (suma de pagos hoy)
- [ ] tickets_atendidos (count pagos hoy)
- [ ] promedio_por_ticket (total/tickets)
- [ ] cajones_disponibles (count)
- [ ] tickets_activos (count)

### 7.4 Reporte Mensual
Retornar:
- [ ] ingresos_por_día (array día → monto)
- [ ] hora_pica (hora con más tickets)
- [ ] tipo_vehiculo_frecuente (count por tipo)
- [ ] total_ingresos_mes
- [ ] total_tickets_mes

### 7.5 Resumen Dashboard
- [ ] cajones_disponibles
- [ ] ingresos_dia
- [ ] tickets_activos
- [ ] ultimos_tickets (últimos 5)

### 7.6 Service/Repository
- [ ] ReportService para lógica de reportes

### 7.7 Policies
- [ ] Admin y Supervisor pueden ver reportes

## Entregables
- Reporte diario completo
- Reporte mensual completo
- Dashboard summary

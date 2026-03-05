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

## Testing

### Pruebas de Reports
```bash
npm run test -- ReportsPage.spec.ts
npm run test -- DailyReport.spec.ts
npm run test -- MonthlyReport.spec.ts
```

### Tests Requeridos
- Carga de datos de reportes
- Renderizado de gráficas
- Filtrado por fecha
- Permisos (Admin/Supervisor)
- Manejo de errores
- Loading states

### Estructura de Tests
```
frontend/tests/
├── pages/
│   ├── ReportsPage.spec.ts
│   ├── DailyReport.spec.ts
│   └── MonthlyReport.spec.ts
└── components/
    ├── IncomeChart.spec.ts
    └── VehicleDistribution.spec.ts
```

### Ejemplo: DailyReport.spec.ts
```typescript
vi.mock('@/api/reports', () => ({
  getDailyReport: vi.fn(),
}))

describe('DailyReport', () => {
  it('displays daily stats', async () => {
    vi.mocked(getDailyReport).mockResolvedValueOnce({
      total_ingresos: 1500,
      tickets_atendidos: 25,
      promedio_por_ticket: 60,
    })

    const wrapper = mount(DailyReport)
    await flushPromises()

    expect(wrapper.text()).toContain('$1,500')
    expect(wrapper.text()).toContain('25')
  })

  it('shows loading state while fetching', () => {
    const wrapper = mount(DailyReport, {
      props: { loading: true },
    })

    expect(wrapper.find('[data-testid="loading"]').exists()).toBe(true)
  })

  it('shows error on failed request', async () => {
    vi.mocked(getDailyReport).mockRejectedValueOnce(new Error('API Error'))

    const wrapper = mount(DailyReport)
    await flushPromises()

    expect(wrapper.text()).toContain('Error')
  })

  it('displays payments table', async () => {
    vi.mocked(getDailyReport).mockResolvedValueOnce({
      payments: [
        { id: 1, total: 40, payment_method: 'efectivo' },
        { id: 2, total: 60, payment_method: 'tarjeta' },
      ],
    })

    const wrapper = mount(DailyReport)
    await flushPromises()

    expect(wrapper.findAll('tbody tr')).toHaveLength(2)
  })
})
```

### Ejemplo: MonthlyReport.spec.ts
```typescript
describe('MonthlyReport', () => {
  it('displays income bar chart', async () => {
    const wrapper = mount(MonthlyReport, {
      props: { data: { ingresos_por_dia: [100, 200, 150] } },
    })

    expect(wrapper.findComponent(BarChart).exists()).toBe(true)
  })

  it('displays vehicle distribution pie chart', async () => {
    const wrapper = mount(MonthlyReport, {
      props: { data: { tipo_vehiculo: { auto: 50, moto: 30, camioneta: 20 } } },
    })

    expect(wrapper.findComponent(PieChart).exists()).toBe(true)
  })

  it('filters data by date range', async () => {
    const wrapper = mount(MonthlyReport)
    
    await wrapper.find('[data-testid="start-date"]').setValue('2024-01-01')
    await wrapper.find('[data-testid="end-date"]').setValue('2024-01-31')
    await wrapper.find('[data-testid="filter-btn"]').trigger('click')

    expect(getMonthlyReport).toHaveBeenCalledWith(
      expect.objectContaining({ start_date: '2024-01-01', end_date: '2024-01-31' })
    )
  })
})
```

## Entregables
- Reporte diario con stats y tabla
- Reporte mensual con gráficas
- Gráficas interactivas
- Acceso restringido por rol

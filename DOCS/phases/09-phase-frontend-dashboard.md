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

## Testing

### Pruebas de Dashboard
```bash
npm run test -- Dashboard.spec.ts
npm run test -- ParkingGrid.spec.ts
npm run test -- StatsCards.spec.ts
```

### Tests Requeridos
- Renderizado de grid de cajones
- Colores según status
- Stats cards muestran datos
- Refresh automático
- Permisos por rol

### Estructura de Tests
```
frontend/tests/
├── components/
│   ├── Dashboard.spec.ts
│   ├── ParkingGrid.spec.ts
│   └── StatsCards.spec.ts
└── composables/
    └── useDashboard.spec.ts
```

### Ejemplo: ParkingGrid.spec.ts
```typescript
import { mount } from '@vue/test-utils'
import ParkingGrid from './ParkingGrid.vue'

describe('ParkingGrid', () => {
  it('renders all parking spaces', () => {
    const spaces = [
      { id: 1, number: 'A1', status: 'disponible', type: 'general' },
      { id: 2, number: 'A2', status: 'ocupado', type: 'general' },
    ]

    const wrapper = mount(ParkingGrid, {
      props: { spaces },
    })

    expect(wrapper.findAll('.parking-space')).toHaveLength(2)
  })

  it('shows green color for available spaces', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', status: 'disponible' }] },
    })

    expect(wrapper.find('.parking-space').classes()).toContain('bg-green-500')
  })

  it('shows red color for occupied spaces', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', status: 'ocupado' }] },
    })

    expect(wrapper.find('.parking-space').classes()).toContain('bg-red-500')
  })

  it('displays space number and type', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', type: 'eléctrico' }] },
    })

    expect(wrapper.text()).toContain('A1')
    expect(wrapper.text()).toContain('eléctrico')
  })
})
```

### Ejemplo: StatsCards.spec.ts
```typescript
describe('StatsCards', () => {
  it('displays income for today', () => {
    const wrapper = mount(StatsCards, {
      props: { summary: { ingresos_dia: 1500 } },
    })

    expect(wrapper.text()).toContain('$1,500.00')
  })

  it('displays active tickets count', () => {
    const wrapper = mount(StatsCards, {
      props: { summary: { tickets_activos: 25 } },
    })

    expect(wrapper.text()).toContain('25')
  })

  it('displays available spaces count', () => {
    const wrapper = mount(StatsCards, {
      props: { summary: { cajones_disponibles: 10 } },
    })

    expect(wrapper.text()).toContain('10')
  })
})
```

## Entregables
- Dashboard con grid de cajones
- Stats en tiempo real
- Navegación funcional

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

## Testing

### Pruebas de Entry/Exit
```bash
npm run test -- EntryPage.spec.ts
npm run test -- ExitPage.spec.ts
npm run test -- PaymentForm.spec.ts
```

### Tests Requeridos
- Registro de entrada con validación
- Búsqueda de ticket por placa
- Cálculo de tarifa
- Proceso de pago
- Mostrar ticket/comprobante

### Estructura de Tests
```
frontend/tests/
├── pages/
│   ├── EntryPage.spec.ts
│   └── ExitPage.spec.ts
├── components/
│   ├── PlateInput.spec.ts
│   ├── PaymentForm.spec.ts
│   └── TicketCard.spec.ts
└── composables/
    └── useEntryExit.spec.ts
```

### Ejemplo: EntryPage.spec.ts
```typescript
import { vi } from 'vitest'

vi.mock('@/api/tickets', () => ({
  createTicket: vi.fn(),
  searchByPlate: vi.fn(),
}))

describe('EntryPage', () => {
  it('registers vehicle entry successfully', async () => {
    vi.mocked(createTicket).mockResolvedValueOnce({
      id: 1,
      plate_number: 'ABC-1234',
      entry_time: new Date().toISOString(),
    })

    const wrapper = mount(EntryPage)
    
    await wrapper.find('[data-testid="plate-input"]').setValue('ABC-1234')
    await wrapper.find('[data-testid="vehicle-select"]').setValue('auto')
    await wrapper.find('[data-testid="space-select"]').setValue(1)
    await wrapper.find('form').trigger('submit')

    expect(createTicket).toHaveBeenCalledWith({
      plate_number: 'ABC-1234',
      vehicle_type: 'auto',
      parking_space_id: 1,
    })

    expect(wrapper.text()).toContain('Entrada registrada')
  })

  it('validates plate format', async () => {
    const wrapper = mount(EntryPage)
    
    await wrapper.find('[data-testid="plate-input"]').setValue('invalid!!')
    await wrapper.find('form').trigger('submit')

    expect(wrapper.text()).toContain('Formato de placa inválido')
  })

  it('converts plate to uppercase', async () => {
    const wrapper = mount(EntryPage)
    
    await wrapper.find('[data-testid="plate-input"]').setValue('abc-1234')
    
    expect(wrapper.find('[data-testid="plate-input"]').element.value).toBe('ABC-1234')
  })
})
```

### Ejemplo: ExitPage.spec.ts
```typescript
describe('ExitPage', () => {
  it('searches and displays ticket by plate', async () => {
    vi.mocked(searchByPlate).mockResolvedValueOnce([
      { id: 1, plate_number: 'ABC-1234', vehicle_type: 'auto', entry_time: '2024-01-01T10:00:00Z' },
    ])

    const wrapper = mount(ExitPage)
    await wrapper.find('[data-testid="search-input"]').setValue('ABC')
    await wrapper.find('[data-testid="search-btn"]').trigger('click')

    expect(searchByPlate).toHaveBeenCalledWith('ABC')
    expect(wrapper.text()).toContain('ABC-1234')
  })

  it('displays calculated fee before payment', async () => {
    vi.mocked(calculateFee).mockResolvedValueOnce({ total: 40, breakdown: { hours: 2, rate: 20 } })

    const wrapper = mount(ExitPage, {
      props: { ticket: { id: 1, entry_time: '2024-01-01T10:00:00Z' } },
    })

    expect(wrapper.text()).toContain('$40.00')
  })

  it('processes payment and shows receipt', async () => {
    vi.mocked(processPayment).mockResolvedValueOnce({ id: 1, total: 40 })

    const wrapper = mount(ExitPage)
    await wrapper.find('[data-testid="payment-method"]').setValue('efectivo')
    await wrapper.find('[data-testid="pay-btn"]').trigger('submit')

    expect(processPayment).toHaveBeenCalledWith({ ticket_id: 1, payment_method: 'efectivo' })
    expect(wrapper.text()).toContain('Comprobante')
  })
})
```

## Entregables
- Registro de entrada funcionando
- Búsqueda de ticket por placa
- Cálculo y pago de tarifa
- Comprobante de pago

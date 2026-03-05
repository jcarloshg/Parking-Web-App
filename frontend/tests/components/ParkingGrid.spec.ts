import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'

const ParkingGrid = {
  template: `
    <div class="parking-grid">
      <div
        v-for="space in spaces"
        :key="space.id"
        class="parking-space"
        :class="getStatusClass(space.status)"
      >
        <span class="space-number">{{ space.number }}</span>
        <span class="space-type">{{ getTypeLabel(space.type) }}</span>
        <span class="space-status">{{ getStatusLabel(space.status) }}</span>
      </div>
    </div>
  `,
  props: {
    spaces: {
      type: Array,
      required: true,
    },
  },
  methods: {
    getStatusClass(status: string) {
      const classes: Record<string, string> = {
        disponible: 'status-available',
        ocupado: 'status-occupied',
        fuera_servicio: 'status-disabled',
      }
      return classes[status] ?? ''
    },
    getStatusLabel(status: string) {
      const labels: Record<string, string> = {
        disponible: 'Disponible',
        ocupado: 'Ocupado',
        fuera_servicio: 'Fuera de servicio',
      }
      return labels[status] ?? status
    },
    getTypeLabel(type: string) {
      const labels: Record<string, string> = {
        general: 'General',
        discapacitado: 'Discapacitado',
        eléctrico: 'Eléctrico',
      }
      return labels[type] ?? type
    },
  },
}

describe('ParkingGrid', () => {
  let wrapper: ReturnType<typeof mount>

  const mockSpaces = [
    { id: 1, number: 'A1', status: 'disponible', type: 'general' },
    { id: 2, number: 'A2', status: 'ocupado', type: 'general' },
    { id: 3, number: 'B1', status: 'fuera_servicio', type: 'discapacitado' },
    { id: 4, number: 'E1', status: 'disponible', type: 'eléctrico' },
  ]

  beforeEach(() => {
    wrapper = mount(ParkingGrid, {
      props: { spaces: mockSpaces },
    })
  })

  it('renders all parking spaces', () => {
    const spaces = wrapper.findAll('.parking-space')
    expect(spaces).toHaveLength(4)
  })

  it('shows green color for available spaces', () => {
    const availableSpace = wrapper.find('.parking-space')
    expect(availableSpace.classes()).toContain('status-available')
  })

  it('shows red color for occupied spaces', () => {
    const occupiedSpace = wrapper.findAll('.parking-space')[1]
    expect(occupiedSpace.classes()).toContain('status-occupied')
  })

  it('shows gray color for disabled spaces', () => {
    const disabledSpace = wrapper.findAll('.parking-space')[2]
    expect(disabledSpace.classes()).toContain('status-disabled')
  })

  it('displays space numbers', () => {
    expect(wrapper.text()).toContain('A1')
    expect(wrapper.text()).toContain('A2')
    expect(wrapper.text()).toContain('B1')
    expect(wrapper.text()).toContain('E1')
  })

  it('displays General type', () => {
    expect(wrapper.text()).toContain('General')
  })

  it('displays Discapacitado type', () => {
    expect(wrapper.text()).toContain('Discapacitado')
  })

  it('displays Eléctrico type', () => {
    expect(wrapper.text()).toContain('Eléctrico')
  })

  it('displays status labels', () => {
    expect(wrapper.text()).toContain('Disponible')
    expect(wrapper.text()).toContain('Ocupado')
    expect(wrapper.text()).toContain('Fuera de servicio')
  })

  it('renders with empty spaces array', () => {
    const emptyWrapper = mount(ParkingGrid, {
      props: { spaces: [] },
    })
    expect(emptyWrapper.findAll('.parking-space')).toHaveLength(0)
  })

  it('handles unknown status gracefully', () => {
    const unknownStatusWrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'X1', status: 'unknown', type: 'general' }] },
    })
    const space = unknownStatusWrapper.find('.parking-space')
    expect(space.classes()).not.toContain('status-available')
    expect(space.classes()).not.toContain('status-occupied')
    expect(space.classes()).not.toContain('status-disabled')
  })

  it('handles unknown type gracefully', () => {
    const unknownTypeWrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'X1', status: 'disponible', type: 'unknown' }] },
    })
    expect(unknownTypeWrapper.text()).toContain('unknown')
  })
})

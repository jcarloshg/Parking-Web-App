import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import TicketCard from '@/components/TicketCard.vue'

describe('TicketCard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  const mockTicket = {
    id: 1,
    plate_number: 'ABC-1234',
    vehicle_type: 'auto' as const,
    entry_time: new Date().toISOString(),
    parking_space_id: 1,
    status: 'activo' as const,
    parking_space: {
      id: 1,
      number: 'A1',
      type: 'general'
    }
  }

  it('renders ticket number', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('Ticket #1')
  })

  it('displays plate number', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('ABC-1234')
  })

  it('displays vehicle type label', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('Automóvil')
  })

  it('displays parking space number', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('A1')
  })

  it('displays status badge', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: { ...mockTicket, status: 'activo' as const } }
    })
    expect(wrapper.find('.ticket-status').text()).toBe('activo')
  })

  it('shows total when showTotal is true', () => {
    const wrapper = mount(TicketCard, {
      props: { 
        ticket: mockTicket, 
        showTotal: true,
        total: 40.00
      }
    })
    expect(wrapper.text()).toContain('Total a pagar')
    expect(wrapper.text()).toContain('$40.00')
  })

  it('displays elapsed time', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('Tiempo:')
  })

  it('formats currency correctly', () => {
    const wrapper = mount(TicketCard, {
      props: { 
        ticket: mockTicket, 
        showTotal: true,
        total: 150.50
      }
    })
    expect(wrapper.text()).toContain('$150.50')
  })

  it('handles different vehicle types - moto', () => {
    const motoTicket = { ...mockTicket, vehicle_type: 'moto' as const }
    const wrapper = mount(TicketCard, {
      props: { ticket: motoTicket }
    })
    expect(wrapper.text()).toContain('Motocicleta')
  })

  it('handles vehicle type - camioneta', () => {
    const camionetaTicket = { ...mockTicket, vehicle_type: 'camioneta' as const }
    const wrapper = mount(TicketCard, {
      props: { ticket: camionetaTicket }
    })
    expect(wrapper.text()).toContain('Camioneta')
  })

  it('displays without parking space', () => {
    const ticketWithoutSpace = { ...mockTicket, parking_space: undefined }
    const wrapper = mount(TicketCard, {
      props: { ticket: ticketWithoutSpace }
    })
    expect(wrapper.exists()).toBe(true)
  })

  it('renders ticket card container', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.find('.ticket-card').exists()).toBe(true)
  })

  it('displays entry time label', () => {
    const wrapper = mount(TicketCard, {
      props: { ticket: mockTicket }
    })
    expect(wrapper.text()).toContain('Entrada:')
  })

  it('handles status - pagado', () => {
    const paidTicket = { ...mockTicket, status: 'pagado' as const }
    const wrapper = mount(TicketCard, {
      props: { ticket: paidTicket }
    })
    expect(wrapper.find('.ticket-status').text()).toBe('pagado')
  })

  it('handles status - cancelado', () => {
    const cancelledTicket = { ...mockTicket, status: 'cancelado' as const }
    const wrapper = mount(TicketCard, {
      props: { ticket: cancelledTicket }
    })
    expect(wrapper.find('.ticket-status').text()).toBe('cancelado')
  })
})

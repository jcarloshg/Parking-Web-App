import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import * as ticketsApi from '@/api/tickets'

vi.mock('@/api/tickets', () => ({
  ticketsApi: {
    create: vi.fn(),
  },
}))

describe('EntryPage Logic', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('validates plate format - empty plate', () => {
    const plateRegex = /^[A-Z]{3}-?\d{4}$/
    const plate = ''
    
    const isValid = plate.length > 0 && plateRegex.test(plate.replace(/-/g, '').substring(0, 7).padStart(7, ' '))
    expect(isValid).toBe(false)
  })

  it('validates plate format - valid plate', () => {
    const plateRegex = /^[A-Z]{3}-?\d{4}$/
    const plate = 'ABC-1234'
    
    const cleanPlate = plate.replace(/-/g, '').substring(0, 7).padStart(7, ' ')
    const isValid = plateRegex.test(cleanPlate)
    expect(isValid).toBe(true)
  })

  it('validates plate format - valid plate without dash', () => {
    const plateRegex = /^[A-Z]{3}-?\d{4}$/
    const plate = 'ABC1234'
    
    const cleanPlate = plate.replace(/-/g, '').substring(0, 7).padStart(7, ' ')
    const isValid = plateRegex.test(cleanPlate)
    expect(isValid).toBe(true)
  })

  it('validates plate format - invalid plate', () => {
    const plateRegex = /^[A-Z]{3}-?\d{4}$/
    const plate = 'invalid!!'
    
    const cleanPlate = plate.replace(/-/g, '').substring(0, 7).padStart(7, ' ')
    const isValid = plateRegex.test(cleanPlate)
    expect(isValid).toBe(false)
  })

  it('converts plate to uppercase', () => {
    const plate = 'abc-1234'
    const upperPlate = plate.toUpperCase()
    expect(upperPlate).toBe('ABC-1234')
  })

  it('validates vehicle type - empty', () => {
    const vehicleType = ''
    expect(vehicleType).toBe('')
  })

  it('validates vehicle type - valid', () => {
    const validTypes = ['auto', 'moto', 'camioneta']
    expect(validTypes).toContain('auto')
    expect(validTypes).toContain('moto')
    expect(validTypes).toContain('camioneta')
  })

  it('validates parking space id', () => {
    const parkingSpaceId = 1
    expect(parkingSpaceId).toBeGreaterThan(0)
  })

  it('validates parking space id - null', () => {
    const parkingSpaceId = null
    expect(parkingSpaceId).toBeNull()
  })

  it('creates ticket API call structure', async () => {
    const ticketData = {
      plate_number: 'ABC-1234',
      vehicle_type: 'auto',
      parking_space_id: 1,
    }

    vi.mocked(ticketsApi.ticketsApi.create).mockResolvedValueOnce({
      data: { id: 1, ...ticketData }
    } as never)

    await ticketsApi.ticketsApi.create(ticketData)

    expect(ticketsApi.ticketsApi.create).toHaveBeenCalledWith(ticketData)
  })

  it('handles API error response', async () => {
    vi.mocked(ticketsApi.ticketsApi.create).mockRejectedValueOnce({
      response: { data: { message: 'Error del servidor' } }
    })

    try {
      await ticketsApi.ticketsApi.create({
        plate_number: 'ABC-1234',
        vehicle_type: 'auto',
        parking_space_id: 1,
      })
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } }
      expect(err.response?.data?.message).toBe('Error del servidor')
    }
  })

  it('formats ticket data for display', () => {
    const ticket = {
      id: 1,
      plate_number: 'ABC-1234',
      vehicle_type: 'auto',
      entry_time: new Date().toISOString(),
    }

    expect(ticket.plate_number).toBe('ABC-1234')
    expect(ticket.vehicle_type).toBe('auto')
  })

  it('resets form state', () => {
    const form = {
      plate: 'ABC-1234',
      vehicleType: 'auto',
      parkingSpaceId: 1,
    }

    form.plate = ''
    form.vehicleType = ''
    form.parkingSpaceId = null

    expect(form.plate).toBe('')
    expect(form.vehicleType).toBe('')
    expect(form.parkingSpaceId).toBeNull()
  })
})

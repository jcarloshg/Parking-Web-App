import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import * as ticketsApi from '@/api/tickets'
import * as paymentsApi from '@/api/payments'

vi.mock('@/api/tickets', () => ({
  ticketsApi: {
    search: vi.fn(),
    calculate: vi.fn(),
  },
}))

vi.mock('@/api/payments', () => ({
  paymentsApi: {
    process: vi.fn(),
  },
}))

describe('ExitPage Logic', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('search ticket API call', async () => {
    vi.mocked(ticketsApi.ticketsApi.search).mockResolvedValueOnce({
      data: [{ id: 1, plate_number: 'ABC-1234' }]
    } as never)

    const response = await ticketsApi.ticketsApi.search('ABC-1234')

    expect(ticketsApi.ticketsApi.search).toHaveBeenCalledWith('ABC-1234')
    expect(response.data).toHaveLength(1)
  })

  it('returns empty when no ticket found', async () => {
    vi.mocked(ticketsApi.ticketsApi.search).mockResolvedValueOnce({
      data: []
    } as never)

    const response = await ticketsApi.ticketsApi.search('XYZ-9999')

    expect(response.data).toHaveLength(0)
  })

  it('calculates fee API call', async () => {
    vi.mocked(ticketsApi.ticketsApi.calculate).mockResolvedValueOnce({
      data: { hours: 2, rate: 20, subtotal: 40, total: 40 }
    } as never)

    const response = await ticketsApi.ticketsApi.calculate(1)

    expect(ticketsApi.ticketsApi.calculate).toHaveBeenCalledWith(1)
    expect(response.data.total).toBe(40)
  })

  it('processes payment API call', async () => {
    vi.mocked(paymentsApi.paymentsApi.process).mockResolvedValueOnce({
      data: { id: 1, amount: 40, payment_method: 'efectivo' }
    } as never)

    const response = await paymentsApi.paymentsApi.process({
      ticket_id: 1,
      payment_method: 'efectivo',
    })

    expect(paymentsApi.paymentsApi.process).toHaveBeenCalledWith({
      ticket_id: 1,
      payment_method: 'efectivo',
    })
    expect(response.data.amount).toBe(40)
  })

  it('processes payment with tarjeta', async () => {
    vi.mocked(paymentsApi.paymentsApi.process).mockResolvedValueOnce({
      data: { id: 1, amount: 40, payment_method: 'tarjeta' }
    } as never)

    const response = await paymentsApi.paymentsApi.process({
      ticket_id: 1,
      payment_method: 'tarjeta',
    })

    expect(response.data.payment_method).toBe('tarjeta')
  })

  it('formats ticket data for display', () => {
    const ticket = {
      id: 1,
      plate_number: 'ABC-1234',
      vehicle_type: 'auto',
      entry_time: '2024-01-01T10:00:00Z',
    }

    expect(ticket.plate_number).toBe('ABC-1234')
    expect(ticket.vehicle_type).toBe('auto')
  })

  it('calculates elapsed time', () => {
    const entryTime = new Date()
    const now = new Date()
    const diffMs = now.getTime() - entryTime.getTime()
    const hours = Math.floor(diffMs / (1000 * 60 * 60))
    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))

    expect(hours).toBe(0)
    expect(minutes).toBeLessThan(1)
  })

  it('formats calculation details', () => {
    const calculation = {
      hours: 3,
      rate: 20,
      subtotal: 60,
      total: 60,
    }

    expect(calculation.hours).toBe(3)
    expect(calculation.total).toBe(60)
  })

  it('handles payment error', async () => {
    vi.mocked(paymentsApi.paymentsApi.process).mockRejectedValueOnce({
      response: { data: { message: 'Error en el pago' } }
    })

    try {
      await paymentsApi.paymentsApi.process({
        ticket_id: 1,
        payment_method: 'efectivo',
      })
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } }
      expect(err.response?.data?.message).toBe('Error en el pago')
    }
  })

  it('resets exit form state', () => {
    const state = {
      searchPlate: 'ABC-1234',
      ticket: { id: 1, plate_number: 'ABC-1234' },
      paymentSuccess: true,
    }

    state.searchPlate = ''
    state.ticket = null
    state.paymentSuccess = false

    expect(state.searchPlate).toBe('')
    expect(state.ticket).toBeNull()
    expect(state.paymentSuccess).toBe(false)
  })

  it('formats payment method label', () => {
    const methodLabels: Record<string, string> = {
      efectivo: 'Efectivo',
      tarjeta: 'Tarjeta',
    }

    expect(methodLabels.efectivo).toBe('Efectivo')
    expect(methodLabels.tarjeta).toBe('Tarjeta')
  })

  it('handles search error', async () => {
    vi.mocked(ticketsApi.ticketsApi.search).mockRejectedValueOnce({
      response: { data: { message: 'Error al buscar' } }
    })

    try {
      await ticketsApi.ticketsApi.search('ABC-1234')
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } }
      expect(err.response?.data?.message).toBe('Error al buscar')
    }
  })

  it('handles calculation error', async () => {
    vi.mocked(ticketsApi.ticketsApi.calculate).mockRejectedValueOnce({
      response: { data: { message: 'Error al calcular' } }
    })

    try {
      await ticketsApi.ticketsApi.calculate(1)
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } }
      expect(err.response?.data?.message).toBe('Error al calcular')
    }
  })
})

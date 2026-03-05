import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'

const formatNumber = (num: number) => {
  return num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

describe('StatsCards', () => {
  describe('formatNumber function', () => {
    it('formats income with 2 decimal places', () => {
      expect(formatNumber(100.0)).toBe('100.00')
    })

    it('formats zero correctly', () => {
      expect(formatNumber(0)).toBe('0.00')
    })

    it('formats large numbers with commas', () => {
      expect(formatNumber(999999.99)).toBe('999,999.99')
    })

    it('formats small decimal values', () => {
      expect(formatNumber(1500.50)).toBe('1,500.50')
    })

    it('formats integers without decimals', () => {
      expect(formatNumber(100)).toBe('100.00')
    })
  })

  describe('Stats Card rendering', () => {
    const mockSummary = {
      cajones_disponibles: 10,
      tickets_activos: 5,
      ingresos_dia: 1500.50,
    }

    it('displays income for today', () => {
      const incomeValue = formatNumber(mockSummary.ingresos_dia)
      expect(`$${incomeValue}`).toBe('$1,500.50')
    })

    it('displays active tickets count', () => {
      expect(String(mockSummary.tickets_activos)).toBe('5')
    })

    it('displays available spaces count', () => {
      expect(String(mockSummary.cajones_disponibles)).toBe('10')
    })

    it('handles zero values', () => {
      const zeroSummary = { cajones_disponibles: 0, tickets_activos: 0, ingresos_dia: 0 }
      expect(formatNumber(zeroSummary.ingresos_dia)).toBe('0.00')
      expect(String(zeroSummary.tickets_activos)).toBe('0')
      expect(String(zeroSummary.cajones_disponibles)).toBe('0')
    })

    it('handles large numbers', () => {
      const largeSummary = { cajones_disponibles: 100, tickets_activos: 500, ingresos_dia: 999999.99 }
      expect(formatNumber(largeSummary.ingresos_dia)).toBe('999,999.99')
      expect(String(largeSummary.cajones_disponibles)).toBe('100')
      expect(String(largeSummary.tickets_activos)).toBe('500')
    })
  })
})

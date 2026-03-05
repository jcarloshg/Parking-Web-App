import api from './index'

export interface ParkingSpace {
  id: number
  number: string
  status: 'disponible' | 'ocupado' | 'fuera_servicio'
  type: 'general' | 'discapacitado' | 'eléctrico'
  created_at: string
  updated_at: string
}

export interface ReportSummary {
  cajones_disponibles: number
  tickets_activos: number
  ingresos_dia: number
  ultimos_tickets?: Array<{
    id: number
    plate: string
    vehicle_type: string
    entry_time: string
    parking_space?: {
      number: string
    }
  }>
}

export const parkingApi = {
  getAll: () => api.get<ParkingSpace[]>('/parking-spaces'),
  getById: (id: number) => api.get<ParkingSpace>(`/parking-spaces/${id}`),
  getAvailable: () => api.get<ParkingSpace[]>('/parking-spaces/available'),
  getAvailableCount: () => api.get<{ count: number }>('/parking-spaces/available-count'),
}

export const reportsApi = {
  getSummary: () => api.get<ReportSummary>('/reports/summary'),
  getDaily: (date?: string) => api.get('/reports/daily', { params: { date } }),
  getMonthly: (year?: number, month?: number) => api.get('/reports/monthly', { params: { year, month } }),
}

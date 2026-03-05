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
    plate_number: string
    vehicle_type: string
    entry_time: string
    exit_time?: string
    status?: string
    parking_space?: {
      number: string
    }
  }>
}

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: Array<{ url: string | null; label: string; page: number | null; active: boolean }>
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export const parkingApi = {
  getAll: () => api.get<PaginatedResponse<ParkingSpace>>('/parking-spaces'),
  getById: (id: number) => api.get<ParkingSpace>(`/parking-spaces/${id}`),
  getAvailable: () => api.get<PaginatedResponse<ParkingSpace>>('/parking-spaces/available'),
  getAvailableCount: () => api.get<{ count: number }>('/parking-spaces/available-count'),
}

export const reportsApi = {
  getSummary: () => api.get<ReportSummary>('/reports/summary'),
  getDaily: (date?: string) => api.get('/reports/daily', { params: { date } }),
  getMonthly: (year?: number, month?: number) => api.get('/reports/monthly', { params: { year, month } }),
}

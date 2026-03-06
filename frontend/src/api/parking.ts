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

export interface DailyReport {
  total_ingresos: number
  tickets_atendidos: number
  promedio_por_ticket: number
  cajones_disponibles: number
  tickets_activos: number
  fecha: string
  tipos_vehiculo: Record<string, number>
  tickets: Array<{
    id: number
    plate_number: string
    vehicle_type: string
    entry_time: string
    exit_time: string | null
    parking_space: string | null
    status: string
    total: number | null
    payment_method: string | null
  }>
}

export interface MonthlyReport {
  ingresos_por_día: Record<string, number>
  hora_pica: number | null
  tipo_vehiculo_frecuente: Record<string, number>
  total_ingresos_mes: number
  total_tickets_mes: number
  año: number
  mes: number
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
  getAll: (config?: { params?: { page?: number; per_page?: number } }) => api.get<PaginatedResponse<ParkingSpace>>('/parking-spaces', config),
  getById: (id: number) => api.get<ParkingSpace>(`/parking-spaces/${id}`),
  getAvailable: () => api.get<PaginatedResponse<ParkingSpace>>('/parking-spaces/available'),
  getAvailableCount: () => api.get<{ count: number }>('/parking-spaces/available-count'),
}

export const reportsApi = {
  getSummary: () => api.get<ReportSummary>('/reports/summary'),
  getDaily: (date?: string) => api.get<{ data: DailyReport }>('/reports/daily', { params: { date } }),
  getMonthly: (year?: number, month?: number) => api.get<{ data: MonthlyReport }>('/reports/monthly', { params: { year, month } }),
}

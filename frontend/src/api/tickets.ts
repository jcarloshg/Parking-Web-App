import api from './index'

export interface Ticket {
  id: number
  plate_number: string
  vehicle_type: 'auto' | 'moto' | 'camioneta'
  entry_time: string
  parking_space_id: number
  status: 'activo' | 'pagado' | 'cancelado'
  parking_space?: {
    id: number
    number: string
    type: string
  }
  created_at: string
  updated_at: string
}

export interface TicketResponse {
  data: Ticket
}

export interface TicketCreate {
  plate_number: string
  vehicle_type: 'auto' | 'moto' | 'camioneta'
  parking_space_id: number
}

export interface TicketSearch {
  plate_number: string
}

export interface TicketCalculation {
  hours: number
  rate: number
  rate_per_hour?: number
  subtotal: number
  total: number
  entry_time?: string
  exit_time?: string
  minutes?: number
  breakdown: {
    hours: number
    rate: number
    subtotal: number
  }
}

export interface TicketCalculationResponse {
  data: TicketCalculation
}

export interface PaginatedTickets {
  data: Ticket[]
  current_page: number
  last_page: number
  total: number
}

export const ticketsApi = {
  create: (data: TicketCreate) => api.post<TicketResponse>('/tickets', data),
  search: (plate: string) => api.get<PaginatedTickets>('/tickets/search', { params: { plate } }),
  getActive: () => api.get<PaginatedTickets>('/tickets/active'),
  getById: (id: number) => api.get<Ticket>(`/tickets/${id}`),
  calculate: (id: number) => api.get<TicketCalculationResponse>(`/tickets/${id}/calculate`),
}

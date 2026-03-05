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
  subtotal: number
  total: number
  breakdown: {
    hours: number
    rate: number
    subtotal: number
  }
}

export const ticketsApi = {
  create: (data: TicketCreate) => api.post<Ticket>('/tickets', data),
  search: (plate: string) => api.get<Ticket[]>('/tickets/search', { params: { plate } }),
  getActive: () => api.get<Ticket[]>('/tickets/active'),
  getById: (id: number) => api.get<Ticket>(`/tickets/${id}`),
  calculate: (id: number) => api.get<TicketCalculation>(`/tickets/${id}/calculate`),
}

import api from './index'

export interface Payment {
  id: number
  ticket_id: number
  amount: number
  payment_method: 'efectivo' | 'tarjeta'
  payment_time: string
  user_id: number
  created_at: string
  updated_at: string
  ticket?: {
    id: number
    plate_number: string
    vehicle_type: string
    entry_time: string
  }
}

export interface PaymentCreate {
  ticket_id: number
  payment_method: 'efectivo' | 'tarjeta'
}

export interface PaymentCalculation {
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

export interface PaymentResponse {
  data: Payment
}

export const paymentsApi = {
  calculate: (ticketId: number) => api.get<{ data: PaymentCalculation }>(`/payments/calculate/${ticketId}`),
  process: (data: PaymentCreate) => api.post<PaymentResponse>('/payments', data),
  getToday: () => api.get<Payment[]>('/payments/today'),
  getById: (id: number) => api.get<Payment>(`/payments/${id}`),
}

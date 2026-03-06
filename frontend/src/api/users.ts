import api from './index'

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'cajero' | 'supervisor'
  created_at: string
  updated_at: string
}

export interface UserCreate {
  name: string
  email: string
  password: string
  role: 'admin' | 'cajero' | 'supervisor'
}

export interface UserUpdate {
  name?: string
  email?: string
  password?: string
  role?: 'admin' | 'cajero' | 'supervisor'
}

export interface PaginatedUsers {
  current_page: number
  data: User[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: Array<{ url: string | null; label: string; active: boolean }>
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export interface PaginatedParkingSpaces {
  current_page: number
  data: Array<{
    id: number
    number: string
    type: 'general' | 'discapacitado' | 'eléctrico'
    status: 'disponible' | 'ocupado' | 'fuera_servicio'
    created_at: string
    updated_at: string
  }>
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: Array<{ url: string | null; label: string; active: boolean }>
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export const usersApi = {
  getAll: (page = 1) => api.get<{ data: PaginatedUsers }>('/users', { params: { page } }),
  getById: (id: number) => api.get<{ data: User }>(`/users/${id}`),
  create: (data: UserCreate) => api.post<{ data: User }>('/users', data),
  update: (id: number, data: UserUpdate) => api.put<{ data: User }>(`/users/${id}`, data),
  delete: (id: number) => api.delete(`/users/${id}`),
}

export const adminParkingApi = {
  getAll: (page = 1) => api.get<{ data: PaginatedParkingSpaces }>('/parking-spaces', { params: { page } }),
  getById: (id: number) => api.get(`/parking-spaces/${id}`),
  create: (data: { number: string; type: 'general' | 'discapacitado' | 'eléctrico' }) => 
    api.post('/parking-spaces', data),
  update: (id: number, data: { number?: string; type?: string; status?: string }) => 
    api.put(`/parking-spaces/${id}`, data),
  delete: (id: number) => api.delete(`/parking-spaces/${id}`),
}

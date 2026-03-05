import api from './index'

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'cajero' | 'supervisor'
}

export interface LoginResponse {
  user: User
  access_token: string
  token_type: string
  expires_in: number
}

export interface LoginRequest {
  email: string
  password: string
}

export const authApi = {
  login: async (data: LoginRequest): Promise<LoginResponse> => {
    const response = await api.post<LoginResponse>('/auth/login', data)
    return response.data
  },

  register: async (data: LoginRequest & { name: string }): Promise<LoginResponse> => {
    const response = await api.post<LoginResponse>('/auth/register', data)
    return response.data
  },

  logout: async (): Promise<void> => {
    await api.post('/auth/logout')
  },

  refresh: async (): Promise<{ token: string }> => {
    const response = await api.post<{ token: string }>('/auth/refresh')
    return response.data
  },

  me: async (): Promise<User> => {
    const response = await api.get<User>('/auth/me')
    return response.data
  },
}

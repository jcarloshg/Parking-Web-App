import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('@/api/auth', () => ({
  authApi: {
    login: vi.fn().mockResolvedValue({
      token: 'test-jwt-token',
      user: { id: 1, name: 'Test User', role: 'admin', email: 'admin@parking.com' },
    }),
    logout: vi.fn().mockResolvedValue(undefined),
    me: vi.fn().mockResolvedValue({ id: 1, name: 'Test User', role: 'admin', email: 'admin@parking.com' }),
  },
}))

vi.stubGlobal('localStorage', {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('has null user and token initially', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
    })

    it('is not authenticated initially', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      expect(store.isAuthenticated).toBe(false)
    })

    it('is not admin, cajero, or supervisor initially', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      expect(store.isAdmin).toBe(false)
      expect(store.isCajero).toBe(false)
      expect(store.isSupervisor).toBe(false)
    })
  })

  describe('login', () => {
    it('sets user and token on successful login', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'admin@parking.com', password: 'password' })

      expect(store.token).toBe('test-jwt-token')
      expect(store.user).toEqual(expect.objectContaining({
        id: 1,
        name: 'Test User',
        role: 'admin',
        email: 'admin@parking.com',
      }))
    })

    it('sets isAuthenticated to true after login', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'admin@parking.com', password: 'password' })

      expect(store.isAuthenticated).toBe(true)
    })

    it('stores token in localStorage', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'admin@parking.com', password: 'password' })

      expect(localStorage.setItem).toHaveBeenCalledWith('token', 'test-jwt-token')
    })

    it('stores user in localStorage', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'admin@parking.com', password: 'password' })

      expect(localStorage.setItem).toHaveBeenCalledWith(
        'user',
        expect.any(String)
      )
    })

    it('sets isAdmin to true for admin role', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'admin@parking.com', password: 'password' })

      expect(store.isAdmin).toBe(true)
    })

    it('sets isCajero to true for cajero role', async () => {
      const { authApi } = await import('@/api/auth')
      vi.mocked(authApi.login).mockResolvedValueOnce({
        token: 'test-token',
        user: { id: 2, name: 'Cajero', role: 'cajero', email: 'cajero@parking.com' },
      })

      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'cajero@parking.com', password: 'password' })

      expect(store.isCajero).toBe(true)
      expect(store.isAdmin).toBe(false)
    })

    it('sets isSupervisor to true for supervisor role', async () => {
      const { authApi } = await import('@/api/auth')
      vi.mocked(authApi.login).mockResolvedValueOnce({
        token: 'test-token',
        user: { id: 3, name: 'Supervisor', role: 'supervisor', email: 'supervisor@parking.com' },
      })

      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.login({ email: 'supervisor@parking.com', password: 'password' })

      expect(store.isSupervisor).toBe(true)
      expect(store.isAdmin).toBe(false)
    })
  })

  describe('logout', () => {
    it('clears user and token on logout', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'test-token'
      store.user = { id: 1, name: 'Test', role: 'admin', email: 'test@test.com' }

      await store.logout()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
    })

    it('sets isAuthenticated to false after logout', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'test-token'

      await store.logout()

      expect(store.isAuthenticated).toBe(false)
    })

    it('removes token from localStorage', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'test-token'

      await store.logout()

      expect(localStorage.removeItem).toHaveBeenCalledWith('token')
    })

    it('removes user from localStorage', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.user = { id: 1, name: 'Test', role: 'admin', email: 'test@test.com' }

      await store.logout()

      expect(localStorage.removeItem).toHaveBeenCalledWith('user')
    })

    it('calls logout API', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'test-token'

      await store.logout()

      const { authApi } = await import('@/api/auth')
      expect(authApi.logout).toHaveBeenCalled()
    })
  })

  describe('initialize', () => {
    it('loads user and token from localStorage', async () => {
      const mockUser = { id: 1, name: 'Test', role: 'admin', email: 'test@test.com' }
      vi.mocked(localStorage.getItem).mockImplementation((key) => {
        if (key === 'token') return 'stored-token'
        if (key === 'user') return JSON.stringify(mockUser)
        return null
      })

      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.initialize()

      expect(store.token).toBe('stored-token')
      expect(store.user).toEqual(mockUser)
    })

    it('does nothing if no token in localStorage', async () => {
      vi.mocked(localStorage.getItem).mockReturnValue(null)

      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.initialize()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
    })
  })

  describe('fetchUser', () => {
    it('fetches and sets user when token exists', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'test-token'

      await store.fetchUser()

      expect(store.user).toEqual(expect.objectContaining({
        id: 1,
        name: 'Test User',
        role: 'admin',
      }))
    })

    it('does nothing when no token', async () => {
      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()

      await store.fetchUser()

      expect(store.user).toBeNull()
    })

    it('clears auth on fetch failure', async () => {
      const { authApi } = await import('@/api/auth')
      vi.mocked(authApi.me).mockRejectedValueOnce(new Error('Unauthorized'))

      const { useAuthStore } = await import('@/stores/auth')
      const store = useAuthStore()
      store.token = 'invalid-token'

      await store.fetchUser()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
    })
  })
})

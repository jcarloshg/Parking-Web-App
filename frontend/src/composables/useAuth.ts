import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()

  const user = computed(() => authStore.user)
  const token = computed(() => authStore.token)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const isAdmin = computed(() => authStore.isAdmin)
  const isCajero = computed(() => authStore.isCajero)
  const isSupervisor = computed(() => authStore.isSupervisor)
  const loading = computed(() => authStore.loading)
  const error = computed(() => authStore.error)

  const login = async (email: string, password: string) => {
    const result = await authStore.login({ email, password })
    await router.push(getRedirectRoute(authStore.user?.role))
    return result
  }

  const logout = async () => {
    await authStore.logout()
    await router.push('/login')
  }

  const getRedirectRoute = (role?: string) => {
    switch (role) {
      case 'admin':
        return '/admin'
      case 'supervisor':
        return '/reports'
      case 'cajero':
        return '/dashboard'
      default:
        return '/dashboard'
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    isCajero,
    isSupervisor,
    loading,
    error,
    login,
    logout,
    initialize: authStore.initialize,
    fetchUser: authStore.fetchUser,
  }
}

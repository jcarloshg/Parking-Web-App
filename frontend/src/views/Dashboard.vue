<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="logo">
        <div class="logo-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="3" x2="9" y2="21"></line>
          </svg>
        </div>
        <h2>Parking</h2>
      </div>
      <nav class="nav-menu">
        <router-link to="/dashboard" class="nav-item" :class="{ active: $route.path === '/dashboard' }">
          <span class="icon">📊</span>
          Dashboard
        </router-link>
        <router-link to="/entry" class="nav-item" :class="{ active: $route.path === '/entry' }">
          <span class="icon">🚗</span>
          Entrada
        </router-link>
        <router-link to="/exit" class="nav-item" :class="{ active: $route.path === '/exit' }">
          <span class="icon">🚙</span>
          Salida
        </router-link>
        <router-link v-if="canViewReports" to="/reports" class="nav-item" :class="{ active: $route.path === '/reports' }">
          <span class="icon">📈</span>
          Reportes
        </router-link>
        <router-link v-if="isAdmin" to="/admin" class="nav-item" :class="{ active: $route.path === '/admin' }">
          <span class="icon">⚙️</span>
          Admin
        </router-link>
      </nav>
    </aside>

    <div class="main-content">
      <header class="header">
        <h1>Dashboard</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">Cerrar Sesión</button>
        </div>
      </header>

      <main class="content" v-if="!isLoading">
        <div class="stats-grid">
          <div class="stat-card income">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
              <h3>Ingresos del Día</h3>
              <p class="stat-value">${{ formatNumber(summary?.ingresos_dia ?? 0) }}</p>
            </div>
          </div>
          <div class="stat-card tickets">
            <div class="stat-icon">🎫</div>
            <div class="stat-info">
              <h3>Tickets Activos</h3>
              <p class="stat-value">{{ summary?.tickets_activos ?? 0 }}</p>
            </div>
          </div>
          <div class="stat-card spaces">
            <div class="stat-icon">🅿️</div>
            <div class="stat-info">
              <h3>Cajones Disponibles</h3>
              <p class="stat-value">{{ summary?.cajones_disponibles ?? 0 }}</p>
            </div>
          </div>
        </div>

        <div class="parking-section">
          <h2>Estado de Cajones</h2>
          <div class="parking-grid">
            <div
              v-for="space in paginatedParkingSpaces"
              :key="space.id"
              class="parking-space"
              :class="getStatusClass(space.status)"
            >
              <span class="space-number">{{ space.number }}</span>
              <span class="space-type">{{ getTypeLabel(space.type) }}</span>
              <span class="space-status">{{ getStatusLabel(space.status) }}</span>
            </div>
          </div>
          <div class="pagination" v-if="totalPages > 1">
            <button 
              @click="changePage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="pagination-btn"
            >
              Anterior
            </button>
            <span class="pagination-info">
              Página {{ currentPage }} de {{ totalPages }}
            </span>
            <button 
              @click="changePage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="pagination-btn"
            >
              Siguiente
            </button>
          </div>
        </div>

        <div v-if="validTickets.length" class="last-ticket-section">
          <h2>Últimos Tickets</h2>
          <div class="tickets-table">
            <table>
              <thead>
                <tr>
                  <th>Placa</th>
                  <th>Tipo</th>
                  <th>Cajón</th>
                  <th>Hora de Entrada</th>
                  <th>Hora de Salida</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in validTickets" :key="ticket.id">
                  <td>{{ ticket.plate_number }}</td>
                  <td>{{ ticket.vehicle_type }}</td>
                  <td>{{ ticket.parking_space?.number ?? '-' }}</td>
                  <td>{{ formatDate(ticket.entry_time) }}</td>
                  <td>{{ ticket.exit_time ? formatDate(ticket.exit_time) : '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="refresh-info">
          <span>Actualizando cada 30 segundos...</span>
          <span class="last-update">Última actualización: {{ lastUpdate }}</span>
        </div>
      </main>

      <main v-else class="content">
        <div class="loading">Cargando datos del dashboard...</div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { parkingApi, reportsApi, type ParkingSpace, type ReportSummary } from '@/api/parking'

const router = useRouter()
const authStore = useAuthStore()

const parkingSpaces = ref<ParkingSpace[]>([])
const summary = ref<ReportSummary | null>(null)
const lastUpdate = ref('')
const refreshInterval = ref<number | null>(null)
const isLoading = ref(true)

const currentPage = ref(1)
const itemsPerPage = ref(20)
const totalSpaces = ref(0)

const validParkingSpaces = computed(() => {
  if (!parkingSpaces.value) return []
  return parkingSpaces.value.filter(space => space != null && typeof space.id === 'number')
})

const paginatedParkingSpaces = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return validParkingSpaces.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(totalSpaces.value / itemsPerPage.value))

const validTickets = computed(() => {
  if (!summary.value?.ultimos_tickets) return []
  const tickets = summary.value.ultimos_tickets.filter(t => t != null && typeof t.id === 'number')
  return tickets.sort((a, b) => {
    const aActive = a.status === 'activo' || !a.exit_time
    const bActive = b.status === 'activo' || !b.exit_time
    if (aActive && !bActive) return -1
    if (!aActive && bActive) return 1
    return 0
  })
})

const isAdmin = computed(() => authStore.user?.role === 'admin')
const canViewReports = computed(() => authStore.user?.role === 'admin' || authStore.user?.role === 'supervisor')
const userName = computed(() => authStore.user?.name ?? 'Usuario')
const userRole = computed(() => {
  if (!authStore.user) return ''
  const roles: Record<string, string> = {
    admin: 'Administrador',
    cajero: 'Cajero',
    supervisor: 'Supervisor'
  }
  return roles[authStore.user.role] ?? authStore.user.role
})

const formatNumber = (num: number) => {
  return num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('es-MX')
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    disponible: 'status-available',
    ocupado: 'status-occupied',
    fuera_servicio: 'status-disabled'
  }
  return classes[status] ?? ''
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    disponible: 'Disponible',
    ocupado: 'Ocupado',
    fuera_servicio: 'Fuera de servicio'
  }
  return labels[status] ?? status
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    general: 'General',
    discapacitado: 'Discapacitado',
    eléctrico: 'Eléctrico'
  }
  return labels[type] ?? type
}

const loadData = async () => {
  try {
    isLoading.value = true
    const [spacesRes, summaryRes] = await Promise.all([
      parkingApi.getAll({ params: { page: currentPage.value, per_page: itemsPerPage.value } }),
      reportsApi.getSummary()
    ])
    parkingSpaces.value = (spacesRes.data.data ?? []).filter(space => space && space.id)
    totalSpaces.value = spacesRes.data.total ?? 0
    summary.value = summaryRes.data
    lastUpdate.value = new Date().toLocaleTimeString('es-MX')
  } catch (err) {
    console.error('Failed to load dashboard data:', err)
  } finally {
    isLoading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadData()
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  // Ensure user is initialized before loading data
  authStore.initialize()
  loadData()
  refreshInterval.value = window.setInterval(loadData, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}

.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 0;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

.sidebar .logo {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar .logo-icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar .logo h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.nav-menu {
  padding: 1rem 0;
  margin-top: 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.2s ease;
  margin: 0.25rem 0.75rem;
  border-radius: 10px;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.15);
  color: white;
}

.nav-item.active {
  background-color: white;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-item .icon {
  font-size: 1.25rem;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 2rem;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
}

.user-role {
  color: #667eea;
  font-size: 0.875rem;
  font-weight: 500;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}

.logout-btn {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.content {
  flex: 1;
  padding: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.stat-icon {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  font-size: 1.75rem;
}

.stat-card.income .stat-icon {
  background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
}

.stat-card.tickets .stat-icon {
  background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
}

.stat-card.spaces .stat-icon {
  background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
}

.stat-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.stat-value {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
}

.parking-section {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-bottom: 2rem;
}

.parking-section h2 {
  margin: 0 0 1.25rem 0;
  font-size: 1.25rem;
  color: #1f2937;
  font-weight: 600;
}

.parking-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
}

.parking-space {
  aspect-ratio: 1;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.5rem;
  transition: all 0.2s ease;
}

.parking-space:hover {
  transform: scale(1.05);
}

.status-available {
  background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
  border: 2px solid #22c55e;
}

.status-occupied {
  background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
  border: 2px solid #ef4444;
}

.status-disabled {
  background: linear-gradient(135deg, #f3f4f6 0%, #d1d5db 100%);
  border: 2px solid #9ca3af;
}

.space-number {
  font-weight: 700;
  font-size: 1.125rem;
  color: #1f2937;
}

.space-type {
  font-size: 0.75rem;
  color: #6b7280;
}

.space-status {
  font-size: 0.625rem;
  color: #6b7280;
}

.last-ticket-section {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-bottom: 2rem;
}

.last-ticket-section h2 {
  margin: 0 0 1.25rem 0;
  font-size: 1.25rem;
  color: #1f2937;
  font-weight: 600;
}

.tickets-table {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.tickets-table table {
  width: 100%;
  border-collapse: collapse;
}

.tickets-table th,
.tickets-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.tickets-table th {
  background-color: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.tickets-table td {
  color: #1f2937;
  font-size: 0.875rem;
}

.tickets-table tbody tr:hover {
  background-color: #f9fafb;
}

.refresh-info {
  display: flex;
  justify-content: space-between;
  color: #9ca3af;
  font-size: 0.875rem;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
  color: #6b7280;
  font-size: 1.125rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  color: #6b7280;
  font-size: 0.875rem;
}
</style>

<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="logo">
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
              v-for="space in validParkingSpaces"
              :key="space.id"
              class="parking-space"
              :class="getStatusClass(space.status)"
            >
              <span class="space-number">{{ space.number }}</span>
              <span class="space-type">{{ getTypeLabel(space.type) }}</span>
              <span class="space-status">{{ getStatusLabel(space.status) }}</span>
            </div>
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

const validParkingSpaces = computed(() => {
  if (!parkingSpaces.value) return []
  return parkingSpaces.value.filter(space => space != null && typeof space.id === 'number')
})

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
      parkingApi.getAll(),
      reportsApi.getSummary()
    ])
    parkingSpaces.value = (spacesRes.data.data ?? []).filter(space => space && space.id)
    summary.value = summaryRes.data
    lastUpdate.value = new Date().toLocaleTimeString('es-MX')
  } catch (err) {
    console.error('Failed to load dashboard data:', err)
  } finally {
    isLoading.value = false
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
  background-color: #f3f4f6;
}

.sidebar {
  width: 250px;
  background-color: #1f2937;
  color: white;
  padding: 1rem 0;
}

.sidebar .logo {
  padding: 1rem;
  border-bottom: 1px solid #374151;
}

.sidebar .logo h2 {
  margin: 0;
  color: #60a5fa;
}

.nav-menu {
  padding: 1rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #d1d5db;
  text-decoration: none;
  transition: background-color 0.2s;
}

.nav-item:hover {
  background-color: #374151;
}

.nav-item.active {
  background-color: #3b82f6;
  color: white;
}

.nav-item .icon {
  margin-right: 0.5rem;
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
  padding: 1rem 2rem;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
}

.header h1 {
  margin: 0;
  font-size: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 500;
}

.user-role {
  color: #6b7280;
  font-size: 0.875rem;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.logout-btn:hover {
  background-color: #dc2626;
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
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  border-radius: 12px;
}

.stat-card.income .stat-icon {
  background-color: #dcfce7;
}

.stat-card.tickets .stat-icon {
  background-color: #dbeafe;
}

.stat-card.spaces .stat-icon {
  background-color: #fef3c7;
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
  color: #111827;
}

.parking-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}

.parking-section h2 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  color: #1e3a5f;
}

.parking-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
}

.parking-space {
  aspect-ratio: 1;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.5rem;
  transition: transform 0.2s;
}

.parking-space:hover {
  transform: scale(1.05);
}

.status-available {
  background-color: #dcfce7;
  border: 2px solid #22c55e;
}

.status-occupied {
  background-color: #fee2e2;
  border: 2px solid #ef4444;
}

.status-disabled {
  background-color: #e5e7eb;
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
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}

.last-ticket-section h2 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  color: #1e3a5f;
}

.tickets-table {
  overflow-x: auto;
}

.tickets-table table {
  width: 100%;
  border-collapse: collapse;
}

.tickets-table th,
.tickets-table td {
  padding: 0.75rem 1rem;
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
</style>

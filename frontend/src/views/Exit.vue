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
        <h1>Registro de Salida</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">Cerrar Sesión</button>
        </div>
      </header>

      <main class="content">
        <div class="exit-page">
          <div class="page-header">
            <p class="subtitle">Busque el ticket del vehículo</p>
          </div>

          <div v-if="!ticket" class="search-container">
            <form @submit.prevent="handleSearch" class="search-form">
              <div class="search-row">
                <PlateInput
                  v-model="searchPlate"
                  placeholder="ABC-1234"
                  test-id="search-input"
                />
                <button
                  type="submit"
                  class="search-button"
                  :disabled="loading || !searchPlate"
                  data-testid="search-btn"
                >
                  {{ loading ? 'Buscando...' : 'Buscar' }}
                </button>
              </div>
            </form>

            <div v-if="error" class="error-message">
              {{ error }}
            </div>

            <div v-if="notFound" class="not-found-message">
              No se encontró ningún ticket con esa placa
            </div>
          </div>

          <div v-else-if="!paymentSuccess" class="payment-container">
            <TicketCard
              :ticket="ticket"
              :show-total="true"
              :total="calculation?.total ?? null"
              test-id="found-ticket"
            />

            <div v-if="calculation" class="calculation-details">
              <h3>Detalle del cálculo</h3>
              <div class="detail-row">
                <span>Tiempo:</span>
                <span>{{ calculation.hours }} hora(s)</span>
              </div>
              <div class="detail-row">
                <span>Tarifa:</span>
                <span>${{ (calculation.rate_per_hour || calculation.rate)?.toFixed(2) }}</span>
              </div>
              <div class="detail-row total">
                <span>Total:</span>
                <span>${{ calculation.total.toFixed(2) }}</span>
              </div>
            </div>

            <div v-else class="calculating">
              Calculando tarifa...
            </div>

            <PaymentForm
              v-model="paymentMethod"
              :disabled="paymentLoading"
              :error="paymentError"
              button-text="Pagar y Salir"
              button-test-id="pay-btn"
              @submit="handlePayment"
            />
          </div>

          <div v-else class="receipt-container">
            <div class="success-icon">✓</div>
            <h2>Pago Exitoso</h2>

            <div class="receipt">
              <h3>Comprobante de Pago</h3>
              <div class="receipt-row">
                <span>Ticket #:</span>
                <span>{{ ticket.id }}</span>
              </div>
              <div class="receipt-row">
                <span>Placa:</span>
                <span>{{ ticket.plate_number }}</span>
              </div>
              <div class="receipt-row">
                <span>Entrada:</span>
                <span>{{ formatDateTime(ticket.entry_time) }}</span>
              </div>
              <div class="receipt-row">
                <span>Salida:</span>
                <span>{{ formatDateTime(paymentData?.payment_time || new Date().toISOString()) }}</span>
              </div>
              <div class="receipt-row">
                <span>Método:</span>
                <span>{{ paymentMethod === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span>
              </div>
              <div class="receipt-row total">
                <span>Total:</span>
                <span>${{ paymentData?.amount.toFixed(2) || calculation?.total.toFixed(2) }}</span>
              </div>
            </div>

            <button
              type="button"
              class="reset-button"
              @click="resetForm"
            >
              Procesar Otra Salida
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ticketsApi, type Ticket, type TicketCalculation } from '@/api/tickets'
import { paymentsApi, type Payment } from '@/api/payments'
import PlateInput from '@/components/PlateInput.vue'
import TicketCard from '@/components/TicketCard.vue'
import PaymentForm from '@/components/PaymentForm.vue'

const router = useRouter()
const authStore = useAuthStore()

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

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const searchPlate = ref('')
const loading = ref(false)
const error = ref('')
const notFound = ref(false)

const ticket = ref<Ticket | null>(null)
const calculation = ref<TicketCalculation | null>(null)
const calculating = ref(false)

const paymentMethod = ref<'efectivo' | 'tarjeta' | null>(null)
const paymentLoading = ref(false)
const paymentError = ref('')
const paymentSuccess = ref(false)
const paymentData = ref<Payment | null>(null)

const handleSearch = async () => {
  if (!searchPlate.value) return

  loading.value = true
  error.value = ''
  notFound.value = false

  try {
    const response = await ticketsApi.search(searchPlate.value.toUpperCase())
    if (response.data.data.length === 0) {
      notFound.value = true
      ticket.value = null
    } else {
      const foundTicket = response.data.data[0]
      if (foundTicket) {
        ticket.value = foundTicket
        await loadCalculation()
      }
    }
  } catch (err: unknown) {
    console.error('Error searching ticket:', err)
    const axiosError = err as { response?: { data?: { message?: string } } }
    error.value = axiosError.response?.data?.message || 'Error al buscar el ticket'
  } finally {
    loading.value = false
  }
}

const loadCalculation = async () => {
  if (!ticket.value) return

  calculating.value = true
  try {
    const response = await ticketsApi.calculate(ticket.value.id)
    console.log('Calculation response:', response.data)
    calculation.value = response.data.data
    console.log('Calculation set:', calculation.value)
  } catch (err) {
    console.error('Error calculating fee:', err)
  } finally {
    calculating.value = false
  }
}

const handlePayment = async () => {
  if (!ticket.value || !paymentMethod.value) return

  paymentLoading.value = true
  paymentError.value = ''

  try {
    const response = await paymentsApi.process({
      ticket_id: ticket.value.id,
      payment_method: paymentMethod.value,
    })
    paymentData.value = response.data.data
    paymentSuccess.value = true
    router.push('/dashboard')
  } catch (err: unknown) {
    console.error('Error processing payment:', err)
    const axiosError = err as { response?: { data?: { message?: string } } }
    paymentError.value = axiosError.response?.data?.message || 'Error al procesar el pago'
  } finally {
    paymentLoading.value = false
  }
}

const formatDateTime = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleString('es-MX', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const resetForm = () => {
  searchPlate.value = ''
  ticket.value = null
  calculation.value = null
  paymentMethod.value = null
  paymentSuccess.value = false
  paymentData.value = null
  error.value = ''
  notFound.value = false
}
</script>

<style scoped>
.exit-page {
  max-width: 600px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #1f2937;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #6b7280;
  font-size: 1rem;
}

.search-container {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.search-form {
  margin-bottom: 1rem;
}

.search-row {
  display: flex;
  gap: 1rem;
}

.search-row > :first-child {
  flex: 1;
}

.search-button {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.search-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.search-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.75rem 1rem;
  background-color: #fef2f2;
  border-radius: 10px;
  border: 1px solid #fecaca;
}

.not-found-message {
  color: #92400e;
  padding: 0.75rem 1rem;
  background-color: #fef3c7;
  border-radius: 10px;
  border: 1px solid #fcd34d;
  text-align: center;
}

.payment-container {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.calculation-details {
  padding: 1.25rem;
  background-color: #f9fafb;
  border-radius: 12px;
}

.calculation-details h3 {
  margin: 0 0 1rem;
  color: #1f2937;
  font-weight: 600;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  color: #4b5563;
}

.detail-row.total {
  border-top: 2px solid #667eea;
  font-weight: 700;
  font-size: 1.1rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
  color: #1f2937;
}

.calculating {
  text-align: center;
  color: #6b7280;
  padding: 1rem;
}

.receipt-container {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  text-align: center;
}

.success-icon {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1.25rem;
  box-shadow: 0 10px 20px -10px rgba(102, 126, 234, 0.5);
}

h2 {
  color: #1f2937;
  margin-bottom: 1.5rem;
  font-weight: 700;
}

.receipt {
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  margin: 1.5rem 0;
  text-align: left;
  background-color: #f9fafb;
}

.receipt h3 {
  text-align: center;
  margin: 0 0 1rem;
  color: #1f2937;
  font-weight: 600;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  color: #4b5563;
}

.receipt-row.total {
  border-top: 2px solid #667eea;
  font-weight: 700;
  font-size: 1.2rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
  color: #1f2937;
}

.reset-button {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

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
</style>
